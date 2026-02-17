<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><?php echo h($mlSupport->translate('Edit Plot')); ?></h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                    <li class="breadcrumb-item"><a href="/admin/plots"><?php echo h($mlSupport->translate('Plots')); ?></a></li>
                    <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Edit Plot')); ?></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <form action="/admin/plots/update/<?php echo h($plot['id']); ?>" method="POST" class="needs-validation" novalidate>
                        <?php echo csrf_field(); ?>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('Plot Number')); ?> <span class="text-danger">*</span></label>
                                <input type="text" name="plot_number" class="form-control" value="<?php echo h($plot['plot_number']); ?>" required>
                                <div class="invalid-feedback"><?php echo h($mlSupport->translate('Please provide a plot number.')); ?></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('Plot Type')); ?></label>
                                <select name="plot_type" class="form-select">
                                    <option value="residential" <?php echo $plot['plot_type'] == 'residential' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Residential')); ?></option>
                                    <option value="commercial" <?php echo $plot['plot_type'] == 'commercial' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Commercial')); ?></option>
                                    <option value="industrial" <?php echo $plot['plot_type'] == 'industrial' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Industrial')); ?></option>
                                    <option value="mixed" <?php echo $plot['plot_type'] == 'mixed' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Mixed Use')); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('Plot Area')); ?></label>
                                <input type="number" name="plot_area" class="form-control" step="0.01" value="<?php echo h($plot['plot_area']); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('Area Unit')); ?></label>
                                <select name="plot_area_unit" class="form-select">
                                    <option value="sqft" <?php echo $plot['plot_area_unit'] == 'sqft' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Sq. Ft.')); ?></option>
                                    <option value="sqyd" <?php echo $plot['plot_area_unit'] == 'sqyd' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Sq. Yd.')); ?></option>
                                    <option value="acre" <?php echo $plot['plot_area_unit'] == 'acre' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Acre')); ?></option>
                                    <option value="bigha" <?php echo $plot['plot_area_unit'] == 'bigha' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Bigha')); ?></option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('Status')); ?></label>
                                <select name="plot_status" class="form-select">
                                    <option value="available" <?php echo $plot['plot_status'] == 'available' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Available')); ?></option>
                                    <option value="booked" <?php echo $plot['plot_status'] == 'booked' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Booked')); ?></option>
                                    <option value="sold" <?php echo $plot['plot_status'] == 'sold' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Sold')); ?></option>
                                    <option value="blocked" <?php echo $plot['plot_status'] == 'blocked' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Blocked')); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('Length (ft)')); ?></label>
                                <input type="number" name="dimensions_length" class="form-control" step="0.01" value="<?php echo h($plot['dimensions_length']); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('Width (ft)')); ?></label>
                                <input type="number" name="dimensions_width" class="form-control" step="0.01" value="<?php echo h($plot['dimensions_width']); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('Base Price')); ?> (<?php echo h($currency_symbol ?? '₹'); ?>)</label>
                                <input type="number" name="base_price" class="form-control" step="0.01" value="<?php echo h($plot['base_price']); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('PLC Amount')); ?> (<?php echo h($currency_symbol ?? '₹'); ?>)</label>
                                <input type="number" name="plc_amount" class="form-control" step="0.01" value="<?php echo h($plot['plc_amount']); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('Total Price')); ?> (<?php echo h($currency_symbol ?? '₹'); ?>)</label>
                                <input type="number" name="total_price" class="form-control" step="0.01" value="<?php echo h($plot['total_price']); ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="corner_plot" id="cornerPlot" <?php echo $plot['corner_plot'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="cornerPlot"><?php echo h($mlSupport->translate('Corner Plot')); ?></label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="park_facing" id="parkFacing" <?php echo $plot['park_facing'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="parkFacing"><?php echo h($mlSupport->translate('Park Facing')); ?></label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="road_facing" id="roadFacing" <?php echo $plot['road_facing'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="roadFacing"><?php echo h($mlSupport->translate('Road Facing')); ?></label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?php echo h($mlSupport->translate('Remarks')); ?></label>
                            <textarea name="remarks" class="form-control" rows="3"><?php echo h($plot['remarks']); ?></textarea>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary btn-lg px-5"><?php echo h($mlSupport->translate('Update Plot')); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
  'use strict'
  var forms = document.querySelectorAll('.needs-validation')
  Array.prototype.slice.call(forms)
    .forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }
        form.classList.add('was-validated')
      }, false)
    })
})()
</script>