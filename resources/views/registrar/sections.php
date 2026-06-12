<?php
require_once __DIR__ . '/../../../app/controllers/registrar/SectionsController.php';
require_once __DIR__ . '/../../../app/helpers/message.php';
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
    <title>Sections | <?php  require_once __DIR__ . '/../../../app/helpers/title.php'; ?></title>
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
      <div class="col md-6">
        <div class="input-group">
          <input type="text" class="form-control" placeholder="Search Sections (e.g., Mahogani...)" id="searchInput">
        </div>
      </div>
      <div class="col-md-6 text-end mt-2 mt-md-0">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSectionModal">Add Section</button>
      </div>
    </div>

    <!-- add section modal -->
    <div class="modal fade" id="addSectionModal" tabindex="-1" aria-labelledby="addSectionModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form action="../../../app/controllers/registrar/SectionsController.php" method="post">
            <div class="modal-header">
              <h5 class="modal-title" id="addSectionModalLabel">Add Section</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label for="section_name" class="form-label">Section Name</label>
                <input 
                  type="text" 
                  class="form-control" 
                  id="section_name" 
                  name="section_name" 
                  placeholder="e.g., Mahogany" 
                  required
                >
              </div>
              <div class="mb-3">
                <label for="section_grade_level" class="form-label">Grade Level</label>
                <input 
                  type="text" 
                  class="form-control" 
                  id="section_grade_level" 
                  name="grade_level" 
                  placeholder="e.g., Grade 5" 
                  required
                >
              </div>
              <!-- dropdown for teacher assignment -->
              <div class="mb-3">
                <label for="adviser_id" class="form-label">Adviser</label>
                <select class="form-select" id="adviser_id" name="adviser_id" required>
                  <option value="">Select Adviser</option>
                  <?php foreach($teachers as $teacher): ?>
                    <option value="<?php echo $teacher['id']; ?>">
                      <?php echo htmlspecialchars($teacher['full_name']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <!-- dropdown for current school year -->
              <div class="mb-3">
                <label for="school_year_id" class="form-label">School Year</label>
                <select class="form-select" id="school_year_id" name="school_year_id" required>
                  <option value="">Select School Year</option>
                  <?php if($sy): ?>
                    <option value="<?php echo $sy['id']; ?>">
                      <?php echo htmlspecialchars($sy['school_year']); ?>
                    </option>
                  <?php endif; ?>
                </select>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                Close
              </button>
              <button type="submit" class="btn btn-primary" name="save_section">
                Save Section
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- edit section modal -->
    <div class="modal fade" id="editSectionModal" tabindex="-1" aria-labelledby="editSectionModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form action="../../../app/controllers/registrar/SectionsController.php" method="post">
            <div class="modal-header">
              <h5 class="modal-title" id="editSectionModalLabel">Edit Section</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <input type="hidden" id="edit_section_id" name="id">
              <div class="mb-3">
                <label for="edit_section_name" class="form-label">Section Name</label>
                <input 
                  type="text" 
                  class="form-control" 
                  id="edit_section_name" 
                  name="section_name" 
                  placeholder="e.g., Mahogany" 
                  required
                >
              </div>
              <div class="mb-3">
                <label for="edit_section_grade_level" class="form-label">Grade Level</label>
                <input 
                  type="text" 
                  class="form-control" 
                  id="edit_section_grade_level" 
                  name="grade_level" 
                  placeholder="e.g., Grade 5" 
                  required
                >
              </div>
              <!-- dropdown for teacher assignment -->
              <div class="mb-3">
                <label for="edit_adviser_id" class="form-label">Adviser</label>
                <select class="form-select" id="edit_adviser_id" name="adviser_id" required>
                  <option value="">Select Adviser</option>
                  <?php foreach($allTeachers as $teacher): ?> 
                    <option value="<?php echo $teacher['id']; ?>">
                      <?php echo htmlspecialchars($teacher['full_name']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <!-- dropdown for current school year -->
              <div class="mb-3">
                <label for="edit_school_year_id" class="form-label">School Year</label>
                <select class="form-select" id="edit_school_year_id" name="school_year_id" required>
                  <option value="">Select School Year</option>
                  <?php if($sy): ?>
                    <option value="<?php echo $sy['id']; ?>">
                      <?php echo htmlspecialchars($sy['school_year']); ?>
                    </option>
                  <?php endif; ?>
                </select>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                Close
              </button>
              <button type="submit" class="btn btn-primary" name="update_section">
                Update Section
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

<!-- view sections modal -->
<div class="modal fade" id="viewSectionModal" tabindex="-1" aria-labelledby="viewSectionModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 rounded-3 overflow-hidden">

      <div class="modal-header border-bottom px-4 py-3">
        <div class="d-flex align-items-center gap-2">
          <div class="d-flex align-items-center justify-content-center bg-primary bg-opacity-10 rounded-2" style="width:34px;height:34px;">
            <i class="bi bi-grid text-primary fs-5"></i>
          </div>
          <h5 class="modal-title mb-0 fw-medium" id="viewSectionModalLabel">Section details</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body px-4 py-3">

        <div class="row g-3 mb-3">
          <div class="col-6">
            <div class="bg-light rounded-2 p-3">
              <p class="text-uppercase text-muted mb-1" style="font-size:11px;letter-spacing:.05em;">Section name</p>
              <p class="mb-0 fw-medium fs-6" id="view_section_name">-</p>
            </div>
          </div>
          <div class="col-6">
            <div class="bg-light rounded-2 p-3">
              <p class="text-uppercase text-muted mb-1" style="font-size:11px;letter-spacing:.05em;">Grade level</p>
              <p class="mb-0 fw-medium fs-6" id="view_section_grade_level">-</p>
            </div>
          </div>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-6">
            <div class="bg-light rounded-2 p-3">
              <p class="text-uppercase text-muted mb-1" style="font-size:11px;letter-spacing:.05em;">Adviser</p>
              <p class="mb-0 fw-medium fs-6" id="view_adviser_name">-</p>
            </div>
          </div>
          <div class="col-6">
            <div class="bg-light rounded-2 p-3">
              <p class="text-uppercase text-muted mb-1" style="font-size:11px;letter-spacing:.05em;">School year</p>
              <p class="mb-0 fw-medium fs-6" id="view_school_year">-</p>
            </div>
          </div>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-6">
            <div class="bg-light rounded-2 p-3">
              <p class="text-uppercase text-muted mb-1" style="font-size:11px;letter-spacing:.05em;">Enrolled students</p>
              <p class="mb-0 fw-medium text-primary" style="font-size:2rem;line-height:1;" id="view_total_students">0</p>
            </div>
          </div>
          <div class="col-6">
            <div class="bg-light rounded-2 p-3">
              <p class="text-uppercase text-muted mb-1" style="font-size:11px;letter-spacing:.05em;">Max capacity</p>
              <p class="mb-0 fw-medium" style="font-size:2rem;line-height:1;" id="view_max_capacity">35</p>
            </div>
          </div>
        </div>

        <div class="bg-light rounded-2 p-3">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <p class="text-uppercase text-muted mb-0" style="font-size:11px;letter-spacing:.05em;">Capacity status</p>
            <span class="fw-semibold text-success" style="font-size:15px;" id="view_capacity_info">0 / 35</span>
          </div>
          <div class="progress rounded-pill mb-2" style="height:18px;">
            <div id="view_capacity_progress"
              class="progress-bar bg-success rounded-pill fw-semibold"
              role="progressbar"
              style="width:0%; font-size:12px; transition: width 0.4s ease;"
              aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
              <span id="view_capacity_percent">0%</span>
            </div>
          </div>
          <p class="text-muted mb-0" style="font-size:12px;" id="view_capacity_label">0% full</p>
        </div>

      </div>

      <div class="modal-footer border-top px-4 py-3">
        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>

    <div class="card mt-4">
      <h5 class="card-header">Sections</h5>
      <div class="table-responsive nowrap">
        <table class="table">
          <thead>
            <tr>
              <th>#</th>
              <th>Section Name</th>
              <th>Grade Level</th>
              <th>Teacher Assigned</th>
              <th>School Year</th>
              <th>Capacity</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
              if(!empty($sections)):
            ?>
              <?php foreach ($sections as $section): ?>
                <tr>
                  <td><?php echo $section['id']; ?></td>
                  <td><?php echo $section['section_name']; ?></td>
                  <td><?php echo $section['grade_level']; ?></td>
                  <td><?php echo $section['adviser_name']; ?></td>
                  <td><?php echo $section['school_year']; ?></td>
                  <td>
                    <span class="badge bg-primary"><?php echo $section['total_students']; ?></span> 
                    / 
                    <span class="badge bg-secondary"><?php echo $section['max_capacity']; ?></span>
                  </td>
                 
                  <td>
                    <button 
                      class="btn btn-sm btn-primary"
                      data-bs-toggle="modal" 
                      data-bs-target="#viewSectionModal"
                      onclick="viewSection(
                        <?php echo $section['id']; ?>,
                        '<?php echo addslashes(htmlspecialchars($section['section_name'])); ?>',
                        '<?php echo addslashes(htmlspecialchars($section['grade_level'])); ?>',
                        '<?php echo addslashes(htmlspecialchars($section['adviser_name'])); ?>',
                        '<?php echo addslashes(htmlspecialchars($section['school_year'])); ?>',
                        <?php echo $section['total_students']; ?>,
                        <?php echo $section['max_capacity']; ?>
                      )"
                    >
                      View
                    </button>

                    <button 
                      class="btn btn-sm btn-primary"
                      data-bs-toggle="modal" 
                      data-bs-target="#editSectionModal"
                      onclick="editFunction(
                        <?php echo $section['id']; ?>,
                        '<?php echo addslashes(htmlspecialchars($section['section_name'])); ?>',
                        '<?php echo addslashes(htmlspecialchars($section['grade_level'])); ?>',
                        <?php echo $section['adviser_id']; ?>,
                        <?php echo $section['school_year_id']; ?>
                      )"
                    >
                      Edit
                    </button>

                    <form method="POST" action="../../../app/controllers/registrar/SectionsController.php" style="display: inline;">
                      <input type="hidden" name="delete_section" value="<?php echo $section['id']; ?>">
                      <button 
                          type="submit" 
                          class="btn btn-sm btn-danger" 
                          onclick="return confirm('Are you sure you want to delete this section?')"
                          >
                          Delete
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="text-center">No sections found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
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
    <script src="../../../public/js/registrar/sections.js"></script>
</body>
</html>