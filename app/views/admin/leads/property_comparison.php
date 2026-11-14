<?php $this->layout = 'layouts/admin'; ?>

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Property Comparison</h1>
  </div>

  <div id="comparisonTable" class="table-responsive">
    <table class="table table-bordered table-hover">
      <thead class="table-light">
        <tr>
          <th scope="col"></th>
          <?php foreach (array_slice($properties, 0, 4) as $prop): ?>
          <th scope="col"><?= htmlspecialchars($prop['title'] ?? '') ?></th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <tr><th scope="row">Price</th>
          <?php foreach (array_slice($properties, 0, 4) as $prop): ?>
          <td>₹<?= number_format($prop['price']) ?></td>
          <?php endforeach; ?>
        </tr>
        <tr><th scope="row">Location</th>
          <?php foreach (array_slice($properties, 0, 4) as $prop): ?>
          <td><?= htmlspecialchars($prop['location'] ?? '') ?></td>
          <?php endforeach; ?>
        </tr>
        <tr><th scope="row">Type</th>
          <?php foreach (array_slice($properties, 0, 4) as $prop): ?>
          <td><?= htmlspecialchars($prop['type'] ?? '') ?></td>
          <?php endforeach; ?>
        </tr>
        <tr><th scope="row">Bedrooms</th>
          <?php foreach (array_slice($properties, 0, 4) as $prop): ?>
          <td><?= $prop['bedrooms'] ?></td>
          <?php endforeach; ?>
        </tr>
        <tr><th scope="row">Bathrooms</th>
          <?php foreach (array_slice($properties, 0, 4) as $prop): ?>
          <td><?= $prop['bathrooms'] ?></td>
          <?php endforeach; ?>
        </tr>
        <tr><th scope="row">Area (sqft)</th>
          <?php foreach (array_slice($properties, 0, 4) as $prop): ?>
          <td><?= number_format($prop['area_sqft']) ?></td>
          <?php endforeach; ?>
        </tr>
        <tr><th scope="row">Status</th>
          <?php foreach (array_slice($properties, 0, 4) as $prop): ?>
          <td><?= htmlspecialchars(ucfirst($prop['status'])) ?></td>
          <?php endforeach; ?>
        </tr>
        <tr><th scope="row">City</th>
          <?php foreach (array_slice($properties, 0, 4) as $prop): ?>
          <td><?= htmlspecialchars($prop['city'] ?? '') ?></td>
          <?php endforeach; ?>
        </tr>
      </tbody>
    </table>
  </div>
</div>
