<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Models\Colony;
use App\Models\District;
use App\Models\Plot;
use App\Models\Milestone;
use App\Models\PriceSlab;
use Illuminate\Support\Str;

class ColonyController extends AdminController
{
    /**
     * Dashboard — all colonies with stats, total value, development status
     * Includes state/district filters from LocationAdminController
     */
    public function index()
    {
        $this->requireAdmin();

        try {
            // Simple query first to verify connection
            $colonies = Colony::all();
            $count = $colonies->count();

            // Get states/districts
            $states = \App\Models\District::distinct()->orderBy('state')->pluck('state', 'state')->toArray();
            $districts = District::whereIn('state', array_keys($states))->get();

            // Calculate total value
            $totalValue = 0;
            foreach ($colonies as $c) {
                $totalValue += $c->plots->sum(fn ($p) => $p->price);
            }

            return $this->render('admin.colonies.index', [
                'colonies' => $colonies,
                'states' => $states,
                'districts' => $districts,
                'totalValue' => $totalValue,
                'selectedState' => null,
                'selectedDistrict' => null,
            ]);
        } catch (\Exception $e) {
            error_log("Colony index error: " . $e->getMessage());
            return $this->render('admin.errors.500', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show — tabbed view combining ColonyController + ColonyPipelineController
     * Tabs: Overview | Development | Pricing | Plots | Milestones
     */
    public function show($slug)
    {
        $this->requireAdmin();

        $colony = Colony::with([
            'district',
            'plots' => function ($q) {
                $q->orderBy('plot_number');
            },
            'milestones',
            'priceSlabs',
            'layoutConfig',
            'pipelineStats' => function ($q) {
                $q->selectRaw('count(*) as total, sum(case when status="completed" then 1 else 0 end) as completed')
                  ->from('milestones');
            },
        ])->where('slug', $slug)->firstOrFail();

        // Development tab data (from ColonyPipelineController)
        $layoutForm = $colony->layoutConfig ? json_decode($colony->layoutConfig, true) : [];
        $developmentCosts = $colony->pipelineStats ?: (object)['total' => 0, 'completed' => 0];

        // Pricing tab data
        $priceSlabs = $colony->priceSlabs->keyBy('rank');

        // Plots tab data
        $allPlots = $colony->plots()->paginate(20);

        // Milestones tab data
        $milestones = $colony->milestones()->orderBy('level')->get();

        return $this->render('admin.colonies.show', [
            'colony' => $colony,
            'layoutForm' => $layoutForm,
            'developmentCosts' => $developmentCosts,
            'priceSlabs' => $priceSlabs,
            'allPlots' => $allPlots,
            'milestones' => $milestones,
        ]);
    }

    /**
     * Create — form combining basic fields from all systems
     */
    public function create()
    {
        $this->requireAdmin();
        $districts = District::all();
        $returnUrl = route('admin.colonies.store');

        return $this->render('admin.colonies.create', [
            'districts' => $districts,
            'returnUrl' => $returnUrl,
        ]);
    }

    /**
     * Store — handle form with all fields
     */
    public function store()
    {
        $this->requireAdmin();

        $validated = request()->validate([
            'name' => 'required|string|max255',
            'slug' => 'required|string|unique:colonies,slug',
            'state' => 'required|string',
            'district_id' => 'required|integer',
            'city' => 'required|string',
            'pincode' => 'required|string|size:6',
            'land_cost' => 'required|numeric',
            'min_price_per_sqft' => 'required|numeric',
            'block_count' => 'required|integer',
            'phase' => 'required|string',
            // Full schema fields from ColonyController
            'description' => 'nullable|string',
            'amenities' => 'nullable|string',
            'key_highlights' => 'nullable|string',
            'gallery' => 'nullable|string', // JSON
            'youtube' => 'nullable|url',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'show_plots_publicly' => 'boolean',
            'is_featured' => 'boolean',
            // Pipeline fields from ColonyPipelineController
            'pipeline_stage' => 'nullable|string',
            'layout_config' => 'nullable|string', // JSON
            'development_cost' => 'nullable|numeric',
        ]);

        $colony = Colony::create(array_merge($validated, [
            'user_id' => auth()->user()->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        return redirect()
            ->route('admin.colonies.show', $colony->slug)
            ->with('success', 'Colony created successfully.');
    }

    /**
     * Edit — form with all fields
     */
    public function edit($slug)
    {
        $this->requireAdmin();

        $colony = Colony::with('district')->where('slug', $slug)->firstOrFail();
        $districts = District::all();

        return $this->render('admin.colonies.edit', [
            'colony' => $colony,
            'districts' => $districts,
        ]);
    }

    /**
     * Update — handle all fields including pipeline
     */
    public function update($slug)
    {
        $this->requireAdmin();

        $colony = Colony::where('slug', $slug)->firstOrFail();

        $validated = request()->validate([
            'name' => 'sometimes|required|string|max255',
            'slug' => 'sometimes|required|string|unique:colonies,slug,' . $colony->id,
            'state' => 'sometimes|required|string',
            'district_id' => 'sometimes|required|integer',
            'city' => 'sometimes|required|string',
            'pincode' => 'sometimes|required|string|size:6',
            'land_cost' => 'sometimes|required|numeric',
            'min_price_per_sqft' => 'sometimes|required|numeric',
            'block_count' => 'sometimes|required|integer',
            'phase' => 'sometimes|required|string',
            'description' => 'nullable|string',
            'amenities' => 'nullable|string',
            'key_highlights' => 'nullable|string',
            'gallery' => 'nullable|string',
            'youtube' => 'nullable|url',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'show_plots_publicly' => 'sometimes|boolean',
            'is_featured' => 'sometimes|boolean',
            'pipeline_stage' => 'nullable|string',
            'layout_config' => 'nullable|string',
            'development_cost' => 'nullable|numeric',
        ]);

        $colony->update($validated);

        return redirect()
            ->route('admin.colonies.show', $colony->slug)
            ->with('success', 'Colony updated successfully.');
    }

    /**
     * Destroy
     */
    public function destroy($slug)
    {
        $this->requireAdmin();

        $colony = Colony::where('slug', $slug)->firstOrFail();
        $colony->delete();

        return redirect()
            ->route('admin.colonies.index')
            ->with('success', 'Colony deleted successfully.');
    }

    // ---- Pipeline Sub-panels (from ColonyPipelineController) ----

    /**
     * Layout form tab
     */
    public function layout($slug)
    {
        $this->requireAdmin();

        $colony = Colony::where('slug', $slug)->firstOrFail();
        $layoutConfig = $colony->layoutConfig ? json_decode($colony->layoutConfig, true) : [];

        return $this->render('admin.colonies._layout_form', [
            'colony' => $colony,
            'layoutConfig' => $layoutConfig,
        ]);
    }

    /**
     * Save layout config
     */
    public function saveLayout($slug)
    {
        $this->requireAdmin();

        $colony = Colony::where('slug', $slug)->firstOrFail();
        $colony->layout_config = json_encode(request('layout_config'));
        $colony->save();

        return back()->with('success', 'Layout configuration saved.');
    }

    /**
     * Pricing dashboard tab
     */
    public function pricing($slug)
    {
        $this->requireAdmin();

        $colony = Colony::where('slug', $slug)->firstOrFail();
        $priceSlabs = $colony->priceSlabs()->get();

        return $this->render('admin.colonies._pricing_dashboard', [
            'colony' => $colony,
            'priceSlabs' => $priceSlabs,
        ]);
    }

    /**
     * Save pricing
     */
    public function savePricing($slug)
    {
        $this->requireAdmin();

        $colony = Colony::where('slug', $slug)->firstOrFail();
        $rank = request('rank');
        $rate = request('rate');
        $min_val = request('min_value');
        $max_val = request('max_value');

        // Upsert price slab
        $colony->priceSlabs()->updateOrCreate(
            ['rank' => $rank],
            ['rate' => $rate, 'min_value' => $min_val, 'max_value' => $max_val]
        );

        return back()->with('success', 'Pricing updated for rank ' . $rank);
    }

    /**
     * Plot list tab
     */
    public function plots($slug)
    {
        $this->requireAdmin();

        $colony = Colony::where('slug', $slug)->firstOrFail();
        $plots = $colony->plots()->paginate(20);

        return $this->render('admin.colonies._plot_list', [
            'colony' => $colony,
            'plots' => $plots,
        ]);
    }

    /**
     * Plot map (Leaflet GeoJSON)
     */
    public function map($slug)
    {
        $this->requireAdmin();

        $colony = Colony::where('slug', $slug)->firstOrFail();
        $plots = $colony->plots()->get(['plot_number', 'area_sqft', 'price', 'status', 'latitude', 'longitude']);

        return $this->render('admin.colonies._plot_map', [
            'colony' => $colony,
            'plots' => $plots,
        ]);
    }

    /**
     * Milestones tab
     */
    public function milestones($slug)
    {
        $this->requireAdmin();

        $colony = Colony::where('slug', $slug)->firstOrFail();
        $milestones = $colony->milestones()->orderBy('level')->get();

        return $this->render('admin.colonies._milestone_list', [
            'colony' => $colony,
            'milestones' => $milestones,
        ]);
    }

    /**
     * Add milestone
     */
    public function addMilestone($slug)
    {
        $this->requireAdmin();

        $colony = Colony::where('slug', $slug)->firstOrFail();
        $level = request('level');
        $name = request('name');
        $target_date = request('target_date');
        $status = request('status', 'pending');

        $colony->milestones()->create([
            'colony_id' => $colony->id,
            'level' => $level,
            'name' => $name,
            'target_date' => $target_date,
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Milestone added.');
    }

    /**
     * Delete milestone
     */
    public function deleteMilestone($slug, $milestoneId)
    {
        $this->requireAdmin();

        $colony = Colony::where('slug', $slug)->firstOrFail();
        $colony->milestones()->where('id', $milestoneId)->delete();

        return back()->with('success', 'Milestone deleted.');
    }
}