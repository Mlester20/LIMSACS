/**
 * Populates the read-only #viewSectionModal (see resources/views/admin/sections.php).
 * Mirrors registrar's public/js/registrar/sections.js viewSection(), minus the
 * add/edit/delete handlers admin doesn't have.
 */
function viewSection(id, section_name, grade_level, adviser_name, school_year, total_students, max_capacity) {
    document.getElementById('view_section_name').textContent = section_name;
    document.getElementById('view_section_grade_level').textContent = grade_level;
    document.getElementById('view_adviser_name').textContent = adviser_name;
    document.getElementById('view_school_year').textContent = school_year;

    const total = parseInt(total_students) || 0;
    const max = parseInt(max_capacity) || 35;
    document.getElementById('view_total_students').textContent = total;
    document.getElementById('view_max_capacity').textContent = max;
    document.getElementById('view_capacity_info').textContent = total + ' / ' + max;

    const percentage = Math.round((total / max) * 100);

    // Progress bar
    const progressBar = document.getElementById('view_capacity_progress');
    if (progressBar) {
        progressBar.style.width = percentage + '%';
        progressBar.setAttribute('aria-valuenow', percentage);
        progressBar.classList.remove('bg-success', 'bg-warning', 'bg-danger');

        if (percentage >= 90) {
            progressBar.classList.add('bg-danger');
        } else if (percentage >= 70) {
            progressBar.classList.add('bg-warning');
        } else {
            progressBar.classList.add('bg-success');
        }
    }

    // Percent label inside bar (only show if wide enough)
    const percentLabel = document.getElementById('view_capacity_percent');
    if (percentLabel) {
        percentLabel.textContent = percentage >= 10 ? percentage + '%' : '';
    }

    // "X% full" label below bar
    const capacityLabel = document.getElementById('view_capacity_label');
    if (capacityLabel) {
        capacityLabel.textContent = percentage + '% full';

        capacityLabel.className = 'mb-0';
        capacityLabel.style.fontSize = '12px';

        if (percentage >= 90) {
            capacityLabel.classList.add('text-danger');
        } else if (percentage >= 70) {
            capacityLabel.classList.add('text-warning');
        } else {
            capacityLabel.classList.add('text-success');
        }
    }

    // Capacity info color (top-right "X / 35")
    const capacityInfo = document.getElementById('view_capacity_info');
    if (capacityInfo) {
        capacityInfo.className = 'fw-semibold';
        capacityInfo.style.fontSize = '15px';

        if (percentage >= 90) {
            capacityInfo.classList.add('text-danger');
        } else if (percentage >= 70) {
            capacityInfo.classList.add('text-warning');
        } else {
            capacityInfo.classList.add('text-success');
        }
    }
}
