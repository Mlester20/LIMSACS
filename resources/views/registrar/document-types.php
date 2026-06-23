<?php
require_once __DIR__ . '/../../../app/controllers/registrar/DocumentTypesController.php';
require_once __DIR__ . '/../../../app/helpers/flashMessage.php';
require_once __DIR__ . '/../../../app/middleware/Auth.php';
AuthRole::allowOnly(['registrar']);
?>

<!DOCTYPE html>
<html
  lang="en"
  class="light-style layout-menu-fixed"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="../../../public/assets/"
  data-template="vertical-menu-template-free"
>
<head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />
    <title>Document Types | <?php  require_once __DIR__ . '/../../../app/helpers/title.php'; ?></title>
    <meta name="description" content="" />
    <link rel="icon" type="image/x-icon" href="../../../public/assets/img/favicon/logo.png" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="../../../public/assets/vendor/fonts/boxicons.css" />
    <link rel="stylesheet" href="../../../public/assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="../../../public/assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="../../../public/assets/css/demo.css" />
    <link rel="stylesheet" href="../../../public/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <script src="../../../public/assets/vendor/js/helpers.js"></script>
    <script src="../../../public/assets/js/config.js"></script>
</head>
<body>

    <?php FlashMessage::showFlash(); ?>

    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>
    <?php require_once __DIR__ . '/partials/topbar.php'; ?>

    <div class="text-end">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDocumentTypeModal">Add Document Type</button>
    </div>

    <div class="modal fade" id="addDocumentTypeModal" tabindex="-1" aria-labelledby="addDocumentTypeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addDocumentTypeModalLabel">Add Document Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="../../../app/controllers/registrar/DocumentTypesController.php" method="post">
                    <?php echo Csrf::field(); ?>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="documentName" class="form-label">Document Name</label>
                            <input type="text" class="form-control" id="documentName" name="document_name" required>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="isRequired" name="is_required">
                            <label class="form-check-label" for="isRequired">Required</label>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="isActive" name="is_active" checked>
                            <label class="form-check-label" for="isActive">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" name="save_document_type">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- document types update modal -->
    <div class="modal fade" id="editDocumentTypeModal" tabindex="-1" aria-labelledby="editDocumentTypeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editDocumentTypeModalLabel">Edit Document Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="../../../app/controllers/registrar/DocumentTypesController.php" method="post">
                    <?php echo Csrf::field(); ?>
                    <input type="hidden" name="document_id" id="editDocumentTypeId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editDocumentName" class="form-label">Document Name</label>
                            <input type="text" class="form-control" id="editDocumentName" name="document_name" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="editIsRequired" name="is_required">
                            <label class="form-check-label" for="editIsRequired">Required</label>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="editIsActive" name="is_active" checked>
                            <label class="form-check-label" for="editIsActive">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" name="update_document_type">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <h5 class="card-header">Document Types</h5>
        <div class="table-responsive nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Document Name</th>
                        <th>Required</th>
                        <th>Active</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($paginated_documentTypes)): ?>
                        <?php foreach($paginated_documentTypes as $index => $documentType): ?>
                            <tr>
                                <td><?php echo ($offset ?? 0) + $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($documentType['document_name']); ?></td>
                                <td><?php echo htmlspecialchars($documentType['is_required'] ? 'Yes' : 'No'); ?></td>
                                <td><?php echo htmlspecialchars($documentType['is_active'] ? 'Yes' : 'No'); ?></td>
                                <td>

                                    <button 
                                        class="btn btn-sm btn-warning"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editDocumentTypeModal"
                                        onclick="editDocumentType(
                                            '<?php echo $documentType['id']; ?>',
                                            '<?php echo htmlspecialchars(addslashes($documentType['document_name'])); ?>',
                                            <?php echo $documentType['is_required'] ? 'true' : 'false'; ?>,
                                            <?php echo $documentType['is_active'] ? 'true' : 'false'; ?>
                                        )"
                                        
                                    >
                                        Edit
                                    </button>

                                    <form action="../../../app/controllers/registrar/DocumentTypesController.php" method="post" style="display: inline";>
                                        <?php echo Csrf::field(); ?>
                                        <input type="hidden" name="document_id" value="<?php echo $documentType['id']; ?>">
                                        <button 
                                            class="btn btn-sm btn-danger"
                                            type="submit" 
                                            name="delete_document_type" 
                                            onclick="return confirm('Are you sure you want to delete this document type?');"
                                        >
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No document types found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="card-footer">
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-end mb-0">

                    <!-- First & Previous -->
                    <?php if ($current_page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=1">First</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $current_page - 1; ?>">Previous</a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link">First</span>
                        </li>
                        <li class="page-item disabled">
                            <span class="page-link">Previous</span>
                        </li>
                    <?php endif; ?>

                    <!-- Page Numbers -->
                    <?php
                        $max_visible = 5;
                        $start_page = max(1, $current_page - floor($max_visible / 2));
                        $end_page   = min($total_pages, $start_page + $max_visible - 1);
                        $start_page = max(1, $end_page - $max_visible + 1);

                        for ($i = $start_page; $i <= $end_page; $i++):
                    ?>
                        <li class="page-item <?php echo ($i === $current_page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <!-- Next & Last -->
                    <?php if ($current_page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $current_page + 1; ?>">Next</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $total_pages; ?>">Last</a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link">Next</span>
                        </li>
                        <li class="page-item disabled">
                            <span class="page-link">Last</span>
                        </li>
                    <?php endif; ?>

                </ul>
            </nav>
            <p class="text-muted mb-0">
                Showing
                <?php echo $total_entries > 0 ? $offset + 1 : 0; ?>
                to
                <?php echo min($current_page * $entries_per_page, $total_entries); ?>
                of
                <?php echo $total_entries; ?>
                entries
            </p>
        </div>
    </div>

    <?php require_once __DIR__ . '/partials/footer.php'; ?>


    <script src="../../../public/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../../../public/assets/vendor/libs/popper/popper.js"></script>
    <script src="../../../public/assets/vendor/js/bootstrap.js"></script>
    <script src="../../../public/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../../../public/assets/vendor/js/menu.js"></script>
    <script src="../../../public/assets/js/main.js"></script>
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <script src="../../../public/js/registrar/document-types.js"></script>
</body>
</html>