<?php
require_once __DIR__ . '/../../../app/helpers/message.php';
require_once __DIR__ . '/../../../app/controllers/registrar/ParentGuardiansController.php';
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
    <title>Guardians | <?php  require_once __DIR__ . '/../../../app/helpers/title.php'; ?></title>
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

    <?php showFlash(); ?>   

    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>
    <?php require_once __DIR__ . '/partials/topbar.php'; ?>

    <div class="row mb-3 align-items-center">
      <div class="col-md-6">
        <div class="input-group">
          <input type="text" class="form-control" placeholder="Search Parent or Guardian (e.g., Juan Dela Cruz)" id="searchInput">
        </div>
      </div>
      
      <div class="col-md-6 text-end mt-2 mt-md-0">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGuardianModal">Add Student/Guardian</button>
      </div>
    </div>

    <!-- add guardian modal-->
    <div class="modal fade" id="addGuardianModal" tabindex="-1" aria-labelledby="addGuardianModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable"> <div class="modal-content">
                <div class="modal-header py-2"> <h6 class="modal-title fw-bold" id="addGuardianModalLabel">Add New Guardian</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="../../../app/controllers/registrar/ParentGuardiansController.php" method="post">
                    <div class="modal-body py-2"> <div class="alert alert-info py-1 px-3 mb-2">
                            <small style="font-size: 0.75rem;">
                                <strong>Note:</strong> Leave blank if not applicable (N/A).
                            </small>
                        </div>

                        <div class="row align-items-center mb-2">
                            <label for="student_id" class="col-sm-2 col-form-label col-form-label-sm fw-bold">Student:</label>
                            <div class="col-sm-10">
                                <select class="form-select form-select-sm" id="student_id" name="student_id" required>
                                    <option value="" disabled selected>Select a student</option>
                                    <?php foreach($students as $student): ?>
                                        <option value="<?php echo $student['id']; ?>">
                                            <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="card border mb-2 shadow-sm">
                            <div class="card-header py-1 bg-light fw-bold text-secondary" style="font-size: 0.85rem;">
                                Father's Information
                            </div>
                            <div class="card-body py-2 px-3">
                                <div class="row g-2"> <div class="col-md-5">
                                        <label class="form-label small mb-1">Father's Name</label>
                                        <input type="text" class="form-control form-control-sm" name="father_name" placeholder="e.g. Juan Dela Cruz">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small mb-1">Occupation</label>
                                        <input type="text" class="form-control form-control-sm" name="father_occupation" placeholder="e.g. Farmer">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">Contact Number</label>
                                        <input type="text" class="form-control form-control-sm" name="father_contact" placeholder="e.g. 09123456789">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border mb-2 shadow-sm">
                            <div class="card-header py-1 bg-light fw-bold text-secondary" style="font-size: 0.85rem;">
                                Mother's Information
                            </div>
                            <div class="card-body py-2 px-3">
                                <div class="row g-2">
                                    <div class="col-md-5">
                                        <label class="form-label small mb-1">Mother's Name</label>
                                        <input type="text" class="form-control form-control-sm" name="mother_name" placeholder="e.g. Maria Dela Cruz">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small mb-1">Occupation</label>
                                        <input type="text" class="form-control form-control-sm" name="mother_occupation" placeholder="e.g. Teacher">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">Contact Number</label>
                                        <input type="text" class="form-control form-control-sm" name="mother_contact" placeholder="e.g. 09123456789">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border mb-2 shadow-sm">
                            <div class="card-header py-1 bg-light fw-bold text-secondary" style="font-size: 0.85rem;">
                                Guardian Information
                            </div>
                            <div class="card-body py-2 px-3">
                                <div class="row g-2">
                                    <div class="col-md-5">
                                        <label class="form-label small mb-1">Guardian's Name</label>
                                        <input type="text" class="form-control form-control-sm" name="guardian_name" placeholder="e.g. Pedro Dela Cruz">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small mb-1">Relationship</label>
                                        <input type="text" class="form-control form-control-sm" name="guardian_relationship" placeholder="e.g. Uncle, Aunt">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">Contact Number</label>
                                        <input type="text" class="form-control form-control-sm" name="guardian_contact" placeholder="e.g. 09123456789">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer py-1"> <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-sm btn-primary px-3" name="save_guardian">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- edit guardian modal-->
    <div class="modal fade" id="editGuardianModal" tabindex="-1" aria-labelledby="editGuardianModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title fw-bold" id="editGuardianModalLabel">Edit Guardian</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="../../../app/controllers/registrar/ParentGuardiansController.php" method="post">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-body py-2">
                        <div class="alert alert-info py-1 px-3 mb-2">
                            <small style="font-size: 0.75rem;">
                                <strong>Note:</strong> Leave blank if not applicable (N/A).
                            </small>
                        </div>

                        <div class="row align-items-center mb-2">
                            <label for="editStudentId" class="col-sm-2 col-form-label col-form-label-sm fw-bold">Student:</label>
                            <div class="col-sm-10">
                                <select class="form-select form-select-sm" id="editStudentId" name="student_id" required>
                                    <option value="" disabled selected>Select a student</option>
                                    <?php foreach($students as $student): ?>
                                        <option value="<?php echo $student['id']; ?>">
                                            <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="card border mb-2 shadow-sm">
                            <div class="card-header py-1 bg-light fw-bold text-secondary" style="font-size: 0.85rem;">
                                Father's Information
                            </div>
                            <div class="card-body py-2 px-3">
                                <div class="row g-2">
                                    <div class="col-md-5">
                                        <label class="form-label small mb-1">Father's Name</label>
                                        <input type="text" class="form-control form-control-sm" name="father_name" id="editFatherName" placeholder="e.g. Juan Dela Cruz">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small mb-1">Occupation</label>
                                        <input type="text" class="form-control form-control-sm" name="father_occupation" id="editFatherOccupation" placeholder="e.g. Engineer">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">Contact Number</label>
                                        <input type="text" class="form-control form-control-sm" name="father_contact" id="editFatherContact" placeholder="e.g. 09123456789">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border mb-2 shadow-sm">
                            <div class="card-header py-1 bg-light fw-bold text-secondary" style="font-size: 0.85rem;">
                                Mother's Information
                            </div>
                            <div class="card-body py-2 px-3">
                                <div class="row g-2">
                                    <div class="col-md-5">
                                        <label class="form-label small mb-1">Mother's Name</label>
                                        <input type="text" class="form-control form-control-sm" name="mother_name" id="editMotherName" placeholder="e.g. Maria Dela Cruz">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small mb-1">Occupation</label>
                                        <input type="text" class="form-control form-control-sm" name="mother_occupation" id="editMotherOccupation" placeholder="e.g. Teacher">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">Contact Number</label>
                                        <input type="text" class="form-control form-control-sm" name="mother_contact" id="editMotherContact" placeholder="e.g. 09123456789">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border mb-2 shadow-sm">
                            <div class="card-header py-1 bg-light fw-bold text-secondary" style="font-size: 0.85rem;">
                                Guardian Information
                            </div>
                            <div class="card-body py-2 px-3">
                                <div class="row g-2">
                                    <div class="col-md-5">
                                        <label class="form-label small mb-1">Guardian's Name</label>
                                        <input type="text" class="form-control form-control-sm" name="guardian_name" id="editGuardianName" placeholder="e.g. Pedro Dela Cruz">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small mb-1">Relationship</label>
                                        <input type="text" class="form-control form-control-sm" name="guardian_relationship" id="editGuardianRelationship" placeholder="e.g. Uncle, Aunt">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">Contact Number</label>
                                        <input type="text" class="form-control form-control-sm" name="guardian_contact" id="editGuardianContact" placeholder="e.g. 09123456789">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer py-1">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-sm btn-primary px-3" name="update_guardian">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div class="card mt-4">
        <h5 class="card-header">Parent & Guardians</h5>
        <div class="table-responsive nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student Name</th>
                        <th>Father Name</th>
                        <th>Mother Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($parentGuardians)): ?>
                            <?php foreach($parentGuardians as $index => $parentGuardian): ?>
                                <tr>
                                    <td><?php echo $pagination['itemsPerPage'] * ($pagination['currentPage'] - 1) + ($index + 1); ?></td>
                                    <td><?php echo htmlspecialchars($parentGuardian['student_first_name']); ?> <?php echo htmlspecialchars($parentGuardian['student_last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($parentGuardian['father_name']); ?></td>
                                    <td><?php echo htmlspecialchars($parentGuardian['mother_name']); ?></td>
                                    <td>
                                        <button 
                                            class="btn btn-sm btn-primary" 
                                            data-bs-toggle="modal"
                                            data-bs-target="#editGuardianModal"
                                            onclick="editParentGuardian(
                                                '<?php echo $parentGuardian['id']; ?>',
                                                '<?php echo $parentGuardian['student_id']; ?>',
                                                '<?php echo addslashes($parentGuardian['father_name']); ?>',
                                                '<?php echo addslashes($parentGuardian['father_occupation']); ?>',
                                                '<?php echo addslashes($parentGuardian['father_contact']); ?>',
                                                '<?php echo addslashes($parentGuardian['mother_name']); ?>',
                                                '<?php echo addslashes($parentGuardian['mother_occupation']); ?>',
                                                '<?php echo addslashes($parentGuardian['mother_contact']); ?>',
                                                '<?php echo addslashes($parentGuardian['guardian_name']); ?>',
                                                '<?php echo addslashes($parentGuardian['guardian_relationship']); ?>',
                                                '<?php echo addslashes($parentGuardian['guardian_contact']); ?>'
                                            )"
                                        >
                                            Edit
                                        </button>

                                                        <button 
                                            class="btn btn-sm btn-info" 
                                            data-bs-toggle="modal"
                                            data-bs-target="#viewGuardianModal"
                                            onclick="viewParentGuardian(
                                                '<?php echo htmlspecialchars($parentGuardian['student_first_name']); ?> <?php echo htmlspecialchars($parentGuardian['student_last_name']); ?>',
                                                '<?php echo htmlspecialchars($parentGuardian['father_name']); ?>',
                                                '<?php echo htmlspecialchars($parentGuardian['father_occupation']); ?>',
                                                '<?php echo htmlspecialchars($parentGuardian['father_contact']); ?>',
                                                '<?php echo htmlspecialchars($parentGuardian['mother_name']); ?>',
                                                '<?php echo htmlspecialchars($parentGuardian['mother_occupation']); ?>',
                                                '<?php echo htmlspecialchars($parentGuardian['mother_contact']); ?>',
                                                '<?php echo htmlspecialchars($parentGuardian['guardian_name']); ?>',
                                                '<?php echo htmlspecialchars($parentGuardian['guardian_relationship']); ?>',
                                                '<?php echo htmlspecialchars($parentGuardian['guardian_contact']); ?>'
                                            )"
                                        >
                                            View
                                        </button>

                                        <form action="../../../app/controllers/registrar/ParentGuardiansController.php" method="post" style="display: inline">
                                            <input type="hidden" name="id" value="<?php echo $parentGuardian['id']; ?>">
                                            <button 
                                                type="submit" 
                                                class="btn btn-sm btn-danger"
                                                name="delete_guardian"
                                                onclick="return confirm('Are you sure you want to delete this guardian?')"
                                                >
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach;?>
                         <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No parent guardians found.</td>
                        </tr>
                        <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- View Guardian Modal -->
        <div class="modal fade" id="viewGuardianModal" tabindex="-1" aria-labelledby="viewGuardianModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header py-2 bg-info text-white">
                        <h6 class="modal-title fw-bold" id="viewGuardianModalLabel">
                            <i class="bx bx-eye"></i> Parent/Guardian Details
                        </h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-3">
                        <div class="row align-items-center mb-3">
                            <label class="col-sm-3 col-form-label fw-bold">Student:</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control form-control-sm" id="viewStudentName" readonly>
                            </div>
                        </div>
    
                        <div class="card border mb-3 shadow-sm">
                            <div class="card-header py-2 bg-light fw-bold text-secondary" style="font-size: 0.85rem;">
                                Father's Information
                            </div>
                            <div class="card-body py-2 px-3">
                                <div class="row g-2">
                                    <div class="col-md-12">
                                        <label class="form-label small mb-1">Father's Name</label>
                                        <input type="text" class="form-control form-control-sm" id="viewFatherName" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small mb-1">Father's Occupation</label>
                                        <input type="text" class="form-control form-control-sm" id="viewFatherOccupation" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small mb-1">Father's Contact</label>
                                        <input type="text" class="form-control form-control-sm" id="viewFatherContact" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
    
                        <div class="card border mb-3 shadow-sm">
                            <div class="card-header py-2 bg-light fw-bold text-secondary" style="font-size: 0.85rem;">
                                Mother's Information
                            </div>
                            <div class="card-body py-2 px-3">
                                <div class="row g-2">
                                    <div class="col-md-12">
                                        <label class="form-label small mb-1">Mother's Name</label>
                                        <input type="text" class="form-control form-control-sm" id="viewMotherName" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small mb-1">Mother's Occupation</label>
                                        <input type="text" class="form-control form-control-sm" id="viewMotherOccupation" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small mb-1">Mother's Contact</label>
                                        <input type="text" class="form-control form-control-sm" id="viewMotherContact" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
    
                        <div class="card border mb-3 shadow-sm">
                            <div class="card-header py-2 bg-light fw-bold text-secondary" style="font-size: 0.85rem;">
                                Guardian Information
                            </div>
                            <div class="card-body py-2 px-3">
                                <div class="row g-2">
                                    <div class="col-md-12">
                                        <label class="form-label small mb-1">Guardian's Name</label>
                                        <input type="text" class="form-control form-control-sm" id="viewGuardianName" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small mb-1">Guardian's Relationship</label>
                                        <input type="text" class="form-control form-control-sm" id="viewGuardianRelationship" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small mb-1">Guardian's Contact</label>
                                        <input type="text" class="form-control form-control-sm" id="viewGuardianContact" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Pagination -->
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">
                        Showing <?php echo (($pagination['currentPage'] - 1) * $pagination['itemsPerPage']) + 1; ?> 
                        to <?php echo min($pagination['currentPage'] * $pagination['itemsPerPage'], $pagination['totalRecords']); ?>
                        of <?php echo $pagination['totalRecords']; ?> records
                    </small>
                </div>
                
                <nav>
                    <ul class="pagination pagination-sm m-0">
                        <!-- Previous Button -->
                        <?php if($pagination['hasPrevPage']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $pagination['currentPage'] - 1; ?>">Previous</a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link">Previous</span>
                            </li>
                        <?php endif; ?>

                        <!-- Page Numbers -->
                        <?php 
                            $startPage = max(1, $pagination['currentPage'] - 2);
                            $endPage = min($pagination['totalPages'], $pagination['currentPage'] + 2);
                            
                            if($startPage > 1): 
                        ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=1">1</a>
                            </li>
                            <?php if($startPage > 2): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for($i = $startPage; $i <= $endPage; $i++): ?>
                            <?php if($i == $pagination['currentPage']): ?>
                                <li class="page-item active">
                                    <span class="page-link"><?php echo $i; ?></span>
                                </li>
                            <?php else: ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if($endPage < $pagination['totalPages']): ?>
                            <?php if($endPage < $pagination['totalPages'] - 1): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $pagination['totalPages']; ?>"><?php echo $pagination['totalPages']; ?></a>
                            </li>
                        <?php endif; ?>

                        <!-- Next Button -->
                        <?php if($pagination['hasNextPage']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $pagination['currentPage'] + 1; ?>">Next</a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link">Next</span>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
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
    <script src="../../../public/js/registrar/parent-guardians.js"></script>
</body>
</html>