<section class="py-5" class="style-30941">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="text-white fw-bold display-5"><i class="fas fa-toolbox me-2"></i><?php echo __('tool_hub_title', [], 'Tools Hub'); ?></h1>
            <p class="text-white-50 fs-5"><?php echo __('tool_hub_subtitle', [], 'Make your property journey smarter â€” all calculators in one place'); ?></p>
        </div>
        <div class="row g-4">
            <?php
            $tools = [
                ['url' => BASE_URL . '/calc', 'gradient' => 'linear-gradient(135deg, #0d9488, #0f766e)', 'icon' => 'fa-calculator', 'title_key' => 'tool_emi_calc', 'title_default' => 'EMI Calculator', 'desc_key' => 'tool_emi_calc_desc', 'desc_default' => 'Calculate home loan EMI and total interest payable'],
                ['url' => BASE_URL . '/stamp-duty-calculator', 'gradient' => 'linear-gradient(135deg, #f093fb, #f5576c)', 'icon' => 'fa-file-contract', 'title_key' => 'tool_stamp_calc', 'title_default' => 'Stamp Duty Calculator', 'desc_key' => 'tool_stamp_calc_desc', 'desc_default' => 'Know the total government charges for property registration'],
                ['url' => BASE_URL . '/plot-size-converter', 'gradient' => 'linear-gradient(135deg, #4facfe, #00f2fe)', 'icon' => 'fa-vector-square', 'title_key' => 'tool_plot_conv', 'title_default' => 'Plot Size Converter', 'desc_key' => 'tool_plot_conv_desc', 'desc_default' => 'Convert between SQFT, Acre, Bigha, Gaj and more units'],
                ['url' => BASE_URL . '/home-loan-eligibility', 'gradient' => 'linear-gradient(135deg, #43e97b, #38f9d7)', 'icon' => 'fa-hand-holding-dollar', 'title_key' => 'tool_loan_elig', 'title_default' => 'Home Loan Eligibility', 'desc_key' => 'tool_loan_elig_desc', 'desc_default' => 'Check how much home loan you can get based on your salary'],
                ['url' => BASE_URL . '/rent-vs-buy', 'gradient' => 'linear-gradient(135deg, #a18cd1, #fbc2eb)', 'icon' => 'fa-scale-balanced', 'title_key' => 'tool_rent_vs_buy', 'title_default' => 'Rent vs Buy Calculator', 'desc_key' => 'tool_rent_vs_buy_desc', 'desc_default' => 'Understand whether renting or buying property is better for you'],
                ['url' => BASE_URL . '/construction-cost-estimator', 'gradient' => 'linear-gradient(135deg, #f7971e, #ffd200)', 'icon' => 'fa-hard-hat', 'title_key' => 'tool_constr_cost', 'title_default' => 'Construction Cost Estimator', 'desc_key' => 'tool_constr_cost_desc', 'desc_default' => 'Estimate house construction cost based on area and quality'],
                ['url' => BASE_URL . '/rental-yield-calculator', 'gradient' => 'linear-gradient(135deg, #11998e, #38ef7d)', 'icon' => 'fa-chart-pie', 'title_key' => 'tool_rental_yield', 'title_default' => 'Rental Yield Calculator', 'desc_key' => 'tool_rental_yield_desc', 'desc_default' => 'Calculate expected rental income and ROI on your property'],
                ['url' => BASE_URL . '/property-tax-calculator', 'gradient' => 'linear-gradient(135deg, #fc5c7d, #6a82fb)', 'icon' => 'fa-file-invoice-dollar', 'title_key' => 'tool_prop_tax', 'title_default' => 'Property Tax Calculator', 'desc_key' => 'tool_prop_tax_desc', 'desc_default' => 'Estimate your annual property tax with detailed breakdown'],
                ['url' => BASE_URL . '/property-valuation', 'gradient' => 'linear-gradient(135deg, #fa709a, #fee140)', 'icon' => 'fa-house-chimney', 'title_key' => 'tool_prop_val', 'title_default' => 'Property Valuation', 'desc_key' => 'tool_prop_val_desc', 'desc_default' => 'Get an instant estimate of your property market value'],
                ['url' => BASE_URL . '/sip-vs-realestate', 'gradient' => 'linear-gradient(135deg, #ffecd2, #fcb69f)', 'icon' => 'fa-chart-line', 'title_key' => 'tool_sip_vs_re', 'title_default' => 'SIP vs Real Estate', 'desc_key' => 'tool_sip_vs_re_desc', 'desc_default' => 'Compare returns between SIP investments and real estate'],
                ['url' => BASE_URL . '/gst-calculator', 'gradient' => 'linear-gradient(135deg, #a8edea, #fed6e3)', 'icon' => 'fa-receipt', 'title_key' => 'tool_gst_calc', 'title_default' => 'GST Calculator', 'desc_key' => 'tool_gst_calc_desc', 'desc_default' => 'See GST on property â€” view base price and GST separately'],
                ['url' => BASE_URL . '/capital-gains-calculator', 'gradient' => 'linear-gradient(135deg, #89f7fe, #66a6ff)', 'icon' => 'fa-coins', 'title_key' => 'tool_cap_gains', 'title_default' => 'Capital Gains Calculator', 'desc_key' => 'tool_cap_gains_desc', 'desc_default' => 'Calculate tax on property sale with capital gains calculator'],
            ];

            foreach ($tools as $tool) {
                include __DIR__ . '/../partials/tool_card.php';
            }
            ?>
        </div>
    </div>
</section>
<?php include __DIR__ . '/../partials/related_tools.php'; ?>