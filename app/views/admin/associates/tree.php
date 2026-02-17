<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><?php echo h($mlSupport->translate('Associate Tree View')); ?></h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                    <li class="breadcrumb-item"><a href="/admin/associates"><?php echo h($mlSupport->translate('Associates')); ?></a></li>
                    <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Tree View')); ?></li>
                </ul>
            </div>
            <div class="col-auto float-end ms-auto">
                <a href="/admin/associates/show/<?php echo h($associate['id']); ?>" class="btn btn-outline-secondary"><i class="fa fa-arrow-left"></i> <?php echo h($mlSupport->translate('Back to Details')); ?></a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title mb-0"><?php echo h($mlSupport->translate('MLM Hierarchy')); ?> - <?php echo h($associate['user_name']); ?> (<?php echo h($associate['associate_code']); ?>)</h4>
                        </div>
                        <div class="col-auto">
                            <div class="btn-group">
                                <button type="button" id="toggleTree" class="btn btn-primary active"><?php echo h($mlSupport->translate('Tree View')); ?></button>
                                <button type="button" id="toggleTable" class="btn btn-outline-primary"><?php echo h($mlSupport->translate('Table View')); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Stats Bar -->
                    <div class="alert alert-info d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <i class="fa fa-info-circle me-2"></i>
                            <?php echo h($mlSupport->translate('Showing downline for')); ?> <strong><?php echo h($associate['user_name']); ?></strong>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-primary me-2"><?php echo h($mlSupport->translate('Directs')); ?>: <?php echo h($associate['downline_count']); ?></span>
                            <span class="badge bg-success"><?php echo h($mlSupport->translate('Total Earnings')); ?>: ₹<?php echo h(number_format($associate['total_earnings'], 2)); ?></span>
                        </div>
                    </div>

                    <!-- Tree View -->
                    <div id="treeContainer" class="tree-view-wrapper">
                        <div class="root-node mb-4">
                            <div class="card border-primary">
                                <div class="card-body p-3">
                                    <h5 class="card-title mb-1"><?php echo h($associate['user_name']); ?></h5>
                                    <p class="card-text mb-0 small text-muted">
                                        <?php echo h($associate['user_email']); ?> | <?php echo h($associate['user_phone']); ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="hierarchy-tree">
                            <?php
                            if (!function_exists('renderTree')) {
                                function renderTree($members, $mlSupport) {
                                    if (empty($members)) return '';

                                    $html = '<ul class="tree-list">';
                                    foreach ($members as $member) {
                                        $hasChildren = !empty($member['children']);
                                        $html .= '<li class="tree-item">';
                                        $html .= '<div class="node-content' . ($hasChildren ? ' collapsible' : '') . '" data-id="' . h($member['id']) . '">';
                                        
                                        // Toggle icon
                                        if ($hasChildren) {
                                            $html .= '<span class="toggle-icon me-2"><i class="fas fa-plus-square"></i></span>';
                                        } else {
                                            $html .= '<span class="ms-4"></span>';
                                        }

                                        $html .= '<div class="node-info">';
                                        $html .= '<span class="fw-bold">' . h($member['user_name']) . '</span>';
                                        $html .= '<span class="badge bg-light text-dark ms-2">' . h($mlSupport->translate('Level')) . ' ' . h($member['level']) . '</span>';
                                        $html .= '<br><small class="text-muted">' . h($member['user_email']) . ' | ' . h($member['user_phone']) . '</small>';
                                        $html .= '<br><span class="text-success small">' . h($mlSupport->translate('Comm')) . ': ₹' . h(number_format($member['total_commission'], 2)) . '</span>';
                                        $html .= '<span class="text-info small ms-3">' . h($mlSupport->translate('Directs')) . ': ' . h($member['direct_downline_count']) . '</span>';
                                        $html .= ' | <a href="/admin/associates/tree/' . h($member['id']) . '" class="small text-primary">' . h($mlSupport->translate('View Team')) . '</a>';
                                        $html .= '</div>';
                                        $html .= '</div>';

                                        if ($hasChildren) {
                                            $html .= '<div class="child-nodes" style="display:none;">';
                                            $html .= renderTree($member['children'], $mlSupport);
                                            $html .= '</div>';
                                        }

                                        $html .= '</li>';
                                    }
                                    $html .= '</ul>';
                                    return $html;
                                }
                            }

                            echo renderTree($tree, $mlSupport);
                            ?>
                        </div>
                    </div>

                    <!-- Table View (Initially Hidden) -->
                    <div id="tableContainer" class="table-responsive" style="display:none;">
                        <table class="table table-striped custom-table mb-0 datatable" id="treeTable">
                            <thead>
                                <tr>
                                    <th><?php echo h($mlSupport->translate('Name')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Email')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Phone')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Level')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Directs')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Earnings')); ?></th>
                                    <th class="text-end"><?php echo h($mlSupport->translate('Action')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (!function_exists('flattenHierarchy')) {
                                    function flattenHierarchy($members, &$flatList) {
                                        foreach ($members as $member) {
                                            $flatList[] = [
                                                'id' => $member['id'],
                                                'name' => $member['user_name'],
                                                'email' => $member['user_email'],
                                                'phone' => $member['user_phone'],
                                                'level' => $member['level'],
                                                'directs' => $member['direct_downline_count'],
                                                'earnings' => $member['total_commission'],
                                                'children' => $member['children'] ?? []
                                            ];
                                            if (!empty($member['children'])) {
                                                flattenHierarchy($member['children'], $flatList);
                                            }
                                        }
                                    }
                                }

                                $flatList = [];
                                flattenHierarchy($tree, $flatList);

                                foreach ($flatList as $item): ?>
                                    <tr>
                                        <td><?php echo h($item['name']); ?></td>
                                        <td><?php echo h($item['email']); ?></td>
                                        <td><?php echo h($item['phone']); ?></td>
                                        <td><span class="badge bg-info"><?php echo h($mlSupport->translate('Level')); ?> <?php echo h($item['level']); ?></span></td>
                                        <td><?php echo h($item['directs']); ?></td>
                                        <td>₹<?php echo h(number_format($item['earnings'], 2)); ?></td>
                                        <td class="text-end">
                                            <a href="/admin/associates/tree/<?php echo h($item['id']); ?>" class="btn btn-sm btn-outline-primary"><?php echo h($mlSupport->translate('View Team')); ?></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.tree-view-wrapper {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.tree-list {
    list-style: none;
    padding-left: 30px;
    border-left: 1px dashed #adb5bd;
}

.tree-item {
    margin-bottom: 15px;
    position: relative;
}

.tree-item::before {
    content: "";
    position: absolute;
    top: 15px;
    left: -30px;
    width: 30px;
    height: 1px;
    border-top: 1px dashed #adb5bd;
}

.node-content {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 10px 15px;
    display: flex;
    align-items: center;
    transition: all 0.2s;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.node-content.collapsible {
    cursor: pointer;
}

.node-content:hover {
    border-color: #007bff;
    background-color: #f0f7ff;
}

.toggle-icon {
    font-size: 1.2rem;
    color: #007bff;
}

.child-nodes {
    margin-top: 15px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle Tree/Table view
    const toggleTree = document.getElementById('toggleTree');
    const toggleTable = document.getElementById('toggleTable');
    const treeContainer = document.getElementById('treeContainer');
    const tableContainer = document.getElementById('tableContainer');

    toggleTree.addEventListener('click', function() {
        treeContainer.style.display = 'block';
        tableContainer.style.display = 'none';
        
        toggleTree.classList.add('active', 'btn-primary');
        toggleTree.classList.remove('btn-outline-primary');
        
        toggleTable.classList.remove('active', 'btn-primary');
        toggleTable.classList.add('btn-outline-primary');
    });

    toggleTable.addEventListener('click', function() {
        treeContainer.style.display = 'none';
        tableContainer.style.display = 'block';
        
        toggleTable.classList.add('active', 'btn-primary');
        toggleTable.classList.remove('btn-outline-primary');
        
        toggleTree.classList.remove('active', 'btn-primary');
        toggleTree.classList.add('btn-outline-primary');

        // Initialize datatable if not already (assuming jQuery is available)
        if (typeof $ !== 'undefined' && $.fn.DataTable && !$.fn.DataTable.isDataTable('#treeTable')) {
            $('#treeTable').DataTable({
                "bFilter": true,
                "paging": true,
                "info": true
            });
        }
    });

    // Toggle nodes using event delegation or direct binding
    const collapsibleNodes = document.querySelectorAll('.node-content.collapsible');
    collapsibleNodes.forEach(node => {
        node.addEventListener('click', function() {
            const parent = this.closest('.tree-item');
            const childNodes = parent.querySelector('.child-nodes');
            const icon = this.querySelector('.toggle-icon i');

            if (childNodes) {
                if (childNodes.style.display === 'none') {
                    childNodes.style.display = 'block';
                    if (icon) {
                        icon.classList.remove('fa-plus-square');
                        icon.classList.add('fa-minus-square');
                    }
                } else {
                    childNodes.style.display = 'none';
                    if (icon) {
                        icon.classList.remove('fa-minus-square');
                        icon.classList.add('fa-plus-square');
                    }
                }
            }
        });
    });
    
    // Auto-expand first level
    const firstLevel = document.querySelector('.hierarchy-tree > .tree-list > .tree-item > .node-content.collapsible');
    if (firstLevel) {
        firstLevel.click();
    }
});
</script>