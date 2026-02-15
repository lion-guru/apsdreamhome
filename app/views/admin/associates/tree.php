<?php
if (!isAdmin()) {
    header("Location: /admin/login");
    exit();
}
$title = $title ?? $mlSupport->translate("Associate Tree View");
require_once ABSPATH . '/resources/views/admin/layouts/header.php';
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title"><?php echo h($mlSupport->translate('Associate Tree View')); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin/dashboard"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                        <li class="breadcrumb-item"><a href="/admin/associates"><?php echo h($mlSupport->translate('Associates')); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Tree View')); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="card-title mb-0"><?php echo h($mlSupport->translate('MLM Hierarchy')); ?> - <?php echo h($associate['user_name']); ?> (<?php echo h($associate['associate_code']); ?>)</h4>
                            </div>
                            <div class="col-auto">
                                <div class="btn-group">
                                    <button id="toggleTree" class="btn btn-primary active"><?php echo h($mlSupport->translate('Tree View')); ?></button>
                                    <button id="toggleTable" class="btn btn-outline-primary"><?php echo h($mlSupport->translate('Table View')); ?></button>
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
                                function renderTree($members, $mlSupport) {
                                    if (empty($members)) return '';

                                    $html = '<ul class="tree-list">';
                                    foreach ($members as $member) {
                                        $hasChildren = !empty($member['children']);
                                        $html .= '<li class="tree-item">';
                                        $html .= '<div class="node-content' . ($hasChildren ? ' collapsible' : '') . '" data-id="' . h($member['id']) . '">';

                                        if ($hasChildren) {
                                            $html .= '<span class="toggle-icon me-2"><i class="fa fa-plus-square-o"></i></span>';
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

                                echo renderTree($hierarchy, $mlSupport);
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
                                    function flattenHierarchy($members, &$flatList) {
                                        foreach ($members as $member) {
                                            $flatList[] = [
                                                'id' => $member['id'],
                                                'name' => $member['user_name'],
                                                'email' => $member['user_email'],
                                                'phone' => $member['user_phone'],
                                                'level' => $member['level'],
                                                'directs' => $member['direct_downline_count'],
                                                'earnings' => $member['total_commission']
                                            ];
                                            if (!empty($member['children'])) {
                                                flattenHierarchy($member['children'], $flatList);
                                            }
                                        }
                                    }

                                    $flatList = [];
                                    flattenHierarchy($hierarchy, $flatList);

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
$(document).ready(function() {
    // Toggle Tree/Table view
    $('#toggleTree').click(function() {
        $('#treeContainer').show();
        $('#tableContainer').hide();
        $(this).addClass('active').removeClass('btn-outline-primary').addClass('btn-primary');
        $('#toggleTable').removeClass('active').addClass('btn-outline-primary').removeClass('btn-primary');
    });

    $('#toggleTable').click(function() {
        $('#treeContainer').hide();
        $('#tableContainer').show();
        $(this).addClass('active').removeClass('btn-outline-primary').addClass('btn-primary');
        $('#toggleTree').removeClass('active').addClass('btn-outline-primary').removeClass('btn-primary');

        // Initialize datatable if not already
        if (!$.fn.DataTable.isDataTable('#treeTable')) {
            $('#treeTable').DataTable({
                "bFilter": true,
                "paging": true,
                "info": true
            });
        }
    });

    // Toggle nodes
    $('.node-content.collapsible').click(function() {
        const childNodes = $(this).next('.child-nodes');
        const icon = $(this).find('.toggle-icon i');

        if (childNodes.is(':visible')) {
            childNodes.slideUp(200);
            icon.removeClass('fa-minus-square-o').addClass('fa-plus-square-o');
        } else {
            childNodes.slideDown(200);
            icon.removeClass('fa-plus-square-o').addClass('fa-minus-square-o');
        }
    });

    // Auto-expand first level
    $('.hierarchy-tree > .tree-list > .tree-item > .node-content.collapsible').trigger('click');
});
</script>

<?php require_once ABSPATH . '/resources/views/admin/layouts/footer.php'; ?>
