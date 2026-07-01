<section class="py-5" style="background: linear-gradient(135deg, #0f172a, #1e3a5f, #1e293b);">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="text-white fw-bold display-5"><i class="fas fa-toolbox me-2"></i><?php echo __('tool_hub_title', [], 'Tools Hub'); ?></h1>
            <p class="text-white-50 fs-5"><?php echo __('tool_hub_subtitle', [], 'Apni property journey ko smart banayein — saare calculators ek jagah'); ?></p>
        </div>
        <div class="row g-4">
            <?php
            $tools = [
                ['url' => '/apsdreamhome/calc', 'gradient' => 'linear-gradient(135deg, #0d9488, #0f766e)', 'icon' => 'fa-calculator', 'title_key' => 'tool_emi_calc', 'title_default' => 'EMI Calculator', 'desc_key' => 'tool_emi_calc_desc', 'desc_default' => 'Home loan EMI aur total interest calculate karein'],
                ['url' => '/apsdreamhome/stamp-duty-calculator', 'gradient' => 'linear-gradient(135deg, #f093fb, #f5576c)', 'icon' => 'fa-file-contract', 'title_key' => 'tool_stamp_calc', 'title_default' => 'Stamp Duty Calculator', 'desc_key' => 'tool_stamp_calc_desc', 'desc_default' => 'Property registration aur stamp duty ka total cost jaanein'],
                ['url' => '/apsdreamhome/plot-size-converter', 'gradient' => 'linear-gradient(135deg, #4facfe, #00f2fe)', 'icon' => 'fa-vector-square', 'title_key' => 'tool_plot_conv', 'title_default' => 'Plot Size Converter', 'desc_key' => 'tool_plot_conv_desc', 'desc_default' => 'SQFT, Acre, Bigha, Gaj — sabhi units mein convert karein'],
                ['url' => '/apsdreamhome/home-loan-eligibility', 'gradient' => 'linear-gradient(135deg, #43e97b, #38f9d7)', 'icon' => 'fa-hand-holding-dollar', 'title_key' => 'tool_loan_elig', 'title_default' => 'Home Loan Eligibility', 'desc_key' => 'tool_loan_elig_desc', 'desc_default' => 'Aapki salary ke hisaab se kitna loan milega check karein'],
                ['url' => '/apsdreamhome/rent-vs-buy', 'gradient' => 'linear-gradient(135deg, #a18cd1, #fbc2eb)', 'icon' => 'fa-scale-balanced', 'title_key' => 'tool_rent_vs_buy', 'title_default' => 'Rent vs Buy Calculator', 'desc_key' => 'tool_rent_vs_buy_desc', 'desc_default' => 'Kya karna better hai? Rent ya property khareedna?'],
                ['url' => '/apsdreamhome/construction-cost-estimator', 'gradient' => 'linear-gradient(135deg, #f7971e, #ffd200)', 'icon' => 'fa-hard-hat', 'title_key' => 'tool_constr_cost', 'title_default' => 'Construction Cost Estimator', 'desc_key' => 'tool_constr_cost_desc', 'desc_default' => 'Ghar banwane ka estimated cost calculate karein plot area aur quality ke hisaab se'],
                ['url' => '/apsdreamhome/rental-yield-calculator', 'gradient' => 'linear-gradient(135deg, #11998e, #38ef7d)', 'icon' => 'fa-chart-pie', 'title_key' => 'tool_rental_yield', 'title_default' => 'Rental Yield Calculator', 'desc_key' => 'tool_rental_yield_desc', 'desc_default' => 'Property ki rental income aur ROI calculate karein'],
                ['url' => '/apsdreamhome/property-tax-calculator', 'gradient' => 'linear-gradient(135deg, #fc5c7d, #6a82fb)', 'icon' => 'fa-file-invoice-dollar', 'title_key' => 'tool_prop_tax', 'title_default' => 'Property Tax Calculator', 'desc_key' => 'tool_prop_tax_desc', 'desc_default' => 'Apni property ka annual tax aur breakdown estimate karein'],
                ['url' => '/apsdreamhome/property-valuation', 'gradient' => 'linear-gradient(135deg, #fa709a, #fee140)', 'icon' => 'fa-house-chimney', 'title_key' => 'tool_prop_val', 'title_default' => 'Property Valuation', 'desc_key' => 'tool_prop_val_desc', 'desc_default' => 'Apni property ki market value turant estimate karein'],
                ['url' => '/apsdreamhome/sip-vs-realestate', 'gradient' => 'linear-gradient(135deg, #ffecd2, #fcb69f)', 'icon' => 'fa-chart-line', 'title_key' => 'tool_sip_vs_re', 'title_default' => 'SIP vs Real Estate', 'desc_key' => 'tool_sip_vs_re_desc', 'desc_default' => 'SIP mein invest karein ya property? Dono ka comparison dekhein'],
                ['url' => '/apsdreamhome/gst-calculator', 'gradient' => 'linear-gradient(135deg, #a8edea, #fed6e3)', 'icon' => 'fa-receipt', 'title_key' => 'tool_gst_calc', 'title_default' => 'GST Calculator', 'desc_key' => 'tool_gst_calc_desc', 'desc_default' => 'Property par GST kitna hai? Base price aur GST alag dekhein'],
                ['url' => '/apsdreamhome/capital-gains-calculator', 'gradient' => 'linear-gradient(135deg, #89f7fe, #66a6ff)', 'icon' => 'fa-coins', 'title_key' => 'tool_cap_gains', 'title_default' => 'Capital Gains Calculator', 'desc_key' => 'tool_cap_gains_desc', 'desc_default' => 'Property bechne par kitna tax lagega? Calculate karein'],
            ];

            foreach ($tools as $tool) {
                include __DIR__ . '/../partials/tool_card.php';
            }
            ?>
        </div>
    </div>
</section>
<?php include __DIR__ . '/../partials/related_tools.php'; ?>