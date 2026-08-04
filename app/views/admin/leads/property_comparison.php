<?php $this->layout = 'layouts/admin'; ?>

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Property Comparison</h1>
  </div>

  <div id="comparisonTable" class="table-responsive">
    <table class="table table-bordered table-hover">
      <thead class="table-light">
        <tr>
          <th></th>
          <?php foreach (array_slice($properties, 0, 4) as $prop): ?>
          <th><?= htmlspecialchars($prop['title']) ?></th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <tr><th>Price</th>
          <?php foreach (array_slice($properties, 0, 4) as $prop): ?>
          <td>₹<?= number_format($prop['price']) ?></td>
          <?php endforeach; ?>
        </tr>
        <tr><th>Location</th>
          <?php foreach (array_slice($properties, 0, 4) as $prop): ?>
          <td><?= htmlspecialchars($prop['location']) ?></td>
          <?php endforeach; ?>
        </tr>
        <tr><th>Type</th>
          <?php foreach (array_slice($properties, 0, 4) as $prop): ?>
          <td><?= htmlspecialchars($prop['type']) ?></td>
          <?php endforeach; ?>
        </tr>
        <tr><th>Bedrooms</th>
          <?php foreach (array_slice($properties, 0, 4) as $prop): ?>
          <td><?= $prop['bedrooms'] ?></td>
          <?php endforeach; ?>
        </tr>
        <tr><th>Bathrooms</th>
          <?php foreach (array_slice($properties, 0, 4) as $prop): ?>
          <td><?= $prop['bathrooms'] ?></td>
          <?php endforeach; ?>
        </tr>
        <tr><th>Area (sqft)</th>
          <?php foreach (array_slice($properties, 0, 4) as $prop): ?>
          <td><?= number_format($prop['area_sqft']) ?></td>
          <?php endforeach; ?>
        </tr>
        <tr><th>Status</th>
          <?php foreach (array_slice($properties, 0, 4) as $prop): ?>
          <td><?= htmlspecialchars(ucfirst($prop['status'])) ?></td>
          <?php endforeach; ?>
        </tr>
        <tr><th>City</th>
          <?php foreach (array_slice($properties, 0, 4) as $prop): ?>
          <td><?= htmlspecialchars($prop['city']) ?></td>
          <?php endforeach; ?>
        </tr>
      </tbody>
    </table>
  </div>
</div>
