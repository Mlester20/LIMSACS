function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
}

const sourceSectionEl = document.getElementById('sourceSection');
const targetSectionEl = document.getElementById('targetSection');
const promotionSummaryEl = document.getElementById('promotionSummary');
const studentChecklistWrapEl = document.getElementById('studentChecklistWrap');
const studentChecklistBodyEl = document.getElementById('studentChecklistBody');
const studentCountLabelEl = document.getElementById('studentCountLabel');
const selectAllEl = document.getElementById('selectAllStudents');
const promoteBtnEl = document.getElementById('promoteBtn');
const promotionResultEl = document.getElementById('promotionResult');

function resetDownstream() {
    targetSectionEl.innerHTML = '<option value="" selected>Loading...</option>';
    targetSectionEl.disabled = true;
    promotionSummaryEl.classList.add('d-none');
    studentChecklistWrapEl.classList.add('d-none');
    studentChecklistBodyEl.innerHTML = '';
    promotionResultEl.classList.add('d-none');
    promotionResultEl.innerHTML = '';
}

if (sourceSectionEl) {
    sourceSectionEl.addEventListener('change', function () {
        const selected = sourceSectionEl.options[sourceSectionEl.selectedIndex];
        if (!selected || !selected.value) return;

        resetDownstream();

        const sourceSectionId = selected.value;
        const sourceSchoolYearId = selected.dataset.schoolYearId;
        const gradeFrom = selected.dataset.gradeLevel;
        const gradeTo = selected.dataset.nextGradeLevel;

        document.getElementById('summaryGradeFrom').textContent = gradeFrom;
        document.getElementById('summaryGradeTo').textContent = gradeTo;
        promotionSummaryEl.classList.remove('d-none');

        // Load target sections (active school year, next grade level)
        $.ajax({
            url: '../../../app/controllers/registrar/EnrollmentController.php',
            type: 'POST',
            data: { get_sections: 1, school_year_id: ACTIVE_SY_ID, grade_level: gradeTo },
            dataType: 'json',
            success: function (sections) {
                if (!sections || sections.length === 0) {
                    targetSectionEl.innerHTML = '<option value="" selected>No ' + gradeTo + ' sections exist yet for this school year</option>';
                    targetSectionEl.disabled = true;
                    return;
                }
                targetSectionEl.innerHTML = '<option value="" selected disabled>Select a target section...</option>' +
                    sections.map(function (s) {
                        const enrolled = parseInt(s.current_enrollment) || 0;
                        const max = parseInt(s.max_students) || 0;
                        const full = max > 0 && enrolled >= max;
                        return '<option value="' + s.id + '"' + (full ? ' disabled' : '') + '>' +
                            escapeHtml(s.grade_level) + ' - ' + escapeHtml(s.section_name) + ' (' + enrolled + ' / ' + max + (full ? ' — FULL' : '') + ')' +
                            '</option>';
                    }).join('');
                targetSectionEl.disabled = false;
            },
            error: function () {
                targetSectionEl.innerHTML = '<option value="" selected>Error loading sections</option>';
            }
        });

        // Load the checklist of students currently enrolled in the source section
        studentCountLabelEl.textContent = 'Loading...';
        $.ajax({
            url: '../../../app/controllers/registrar/EnrollmentController.php',
            type: 'POST',
            data: { get_enrolled_for_promotion: 1, section_id: sourceSectionId, school_year_id: sourceSchoolYearId },
            dataType: 'json',
            success: function (students) {
                if (!students || students.length === 0) {
                    studentChecklistBodyEl.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-3">No enrolled students in this section.</td></tr>';
                    studentCountLabelEl.textContent = '0 students';
                    studentChecklistWrapEl.classList.remove('d-none');
                    return;
                }

                studentChecklistBodyEl.innerHTML = students.map(function (s) {
                    const fullName = (s.last_name || '') + ', ' + (s.first_name || '');
                    return '<tr>' +
                        '<td><input type="checkbox" class="form-check-input student-check" value="' + s.student_id + '" checked></td>' +
                        '<td>' + escapeHtml(fullName) + '</td>' +
                        '<td>' + escapeHtml(s.lrn || 'N/A') + '</td>' +
                        '</tr>';
                }).join('');

                selectAllEl.checked = true;
                updateStudentCount();
                studentChecklistWrapEl.classList.remove('d-none');
            },
            error: function () {
                studentChecklistBodyEl.innerHTML = '<tr><td colspan="3" class="text-center text-danger py-3">Error loading students.</td></tr>';
                studentChecklistWrapEl.classList.remove('d-none');
            }
        });
    });
}

if (selectAllEl) {
    selectAllEl.addEventListener('change', function () {
        document.querySelectorAll('.student-check').forEach(function (cb) {
            cb.checked = selectAllEl.checked;
        });
        updateStudentCount();
    });
}

document.addEventListener('change', function (e) {
    if (e.target.classList.contains('student-check')) {
        updateStudentCount();
    }
});

function updateStudentCount() {
    const checks = document.querySelectorAll('.student-check');
    const checkedCount = document.querySelectorAll('.student-check:checked').length;
    studentCountLabelEl.textContent = checkedCount + ' of ' + checks.length + ' selected';
}

function submitPromotion() {
    const sourceSectionId = sourceSectionEl.value;
    const targetSectionId = targetSectionEl.value;
    if (!sourceSectionId || !targetSectionId) {
        alert('Select a target section first.');
        return;
    }

    const studentIds = Array.from(document.querySelectorAll('.student-check:checked')).map(function (cb) { return cb.value; });
    if (studentIds.length === 0) {
        alert('Select at least one student to promote.');
        return;
    }

    const targetLabel = targetSectionEl.options[targetSectionEl.selectedIndex].text;
    if (!confirm('Promote ' + studentIds.length + ' student(s) to ' + targetLabel + '?')) {
        return;
    }

    promoteBtnEl.disabled = true;
    promoteBtnEl.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Promoting...';

    $.ajax({
        url: '../../../app/controllers/registrar/EnrollmentController.php',
        type: 'POST',
        data: {
            promote_students: 1,
            csrf_token: getCsrfToken(),
            source_section_id: sourceSectionId,
            target_section_id: targetSectionId,
            student_ids: studentIds
        },
        dataType: 'json',
        success: function (result) {
            promoteBtnEl.disabled = false;
            promoteBtnEl.innerHTML = '<i class="bx bx-up-arrow-alt me-1"></i>Promote Selected Students';

            let html = '<div class="alert ' + (result.success ? 'alert-success' : 'alert-warning') + '">' + escapeHtml(result.message) + '</div>';
            if (result.skipped && result.skipped.length > 0) {
                html += '<div class="alert alert-light border"><strong>Skipped:</strong><ul class="mb-0">' +
                    result.skipped.map(function (s) { return '<li>Student #' + s.student_id + ' — ' + escapeHtml(s.reason) + '</li>'; }).join('') +
                    '</ul></div>';
            }
            promotionResultEl.innerHTML = html;
            promotionResultEl.classList.remove('d-none');

            // Remove successfully promoted students from the checklist so the
            // registrar can see what's left without re-fetching the page.
            document.querySelectorAll('.student-check:checked').forEach(function (cb) {
                cb.closest('tr').remove();
            });
            updateStudentCount();
        },
        error: function () {
            promoteBtnEl.disabled = false;
            promoteBtnEl.innerHTML = '<i class="bx bx-up-arrow-alt me-1"></i>Promote Selected Students';
            promotionResultEl.innerHTML = '<div class="alert alert-danger">An error occurred while promoting students.</div>';
            promotionResultEl.classList.remove('d-none');
        }
    });
}

function escapeHtml(text) {
    if (!text) return '';
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return String(text).replace(/[&<>"']/g, function (m) { return map[m]; });
}
