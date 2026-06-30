/**
 * script.js
 * Front‑end interactions – no external libraries required.
 */
document.addEventListener('DOMContentLoaded', function () {
    highlightSidebarLink();
    setupPasswordToggles();
    setupDeleteConfirmations();
    setupDynamicSubjectFiltering();   // client‑side filtering
    setupAlertDismiss();
    setupFormValidations();
});

/* -------------------------------------------------------
   1. SIDEBAR ACTIVE LINK
   ------------------------------------------------------- */
function highlightSidebarLink() {
    const currentPath = window.location.pathname;
    const links = document.querySelectorAll('.sidebar .nav a');
    links.forEach(link => {
        const href = link.getAttribute('href');
        if (href && currentPath.includes(href)) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });
}

/* -------------------------------------------------------
   2. PASSWORD SHOW / HIDE
   ------------------------------------------------------- */
function setupPasswordToggles() {
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function () {
            const target = document.querySelector(this.getAttribute('data-target'));
            if (!target) return;
            const isPassword = target.getAttribute('type') === 'password';
            target.setAttribute('type', isPassword ? 'text' : 'password');

            // Change the button text / emoji to indicate state
            this.textContent = isPassword ? '🙈' : '👁️';
        });
    });
}

/* -------------------------------------------------------
   3. DELETE CONFIRMATION
   ------------------------------------------------------- */
function setupDeleteConfirmations() {
    document.querySelectorAll('.confirm-delete').forEach(btn => {
        btn.addEventListener('click', function (e) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
}

/* -------------------------------------------------------
   4. DYNAMIC SUBJECT FILTERING (Client‑side)
      - Year of Study (id="year_of_study")
      - Semester (id="semester")
      - Subject dropdown (id="subject_id")
      - All subjects are embedded in a <script> tag with id="allSubjectsData"
        as a JSON array: [{subject_id, subject_code, subject_name, year_of_study, semester}, ...]
   ------------------------------------------------------- */
function setupDynamicSubjectFiltering() {
    const yearSelect  = document.getElementById('year_of_study');
    const semSelect   = document.getElementById('semester');
    const subjectDrop = document.getElementById('subject_id');
    if (!yearSelect || !semSelect || !subjectDrop) return;

    // Retrieve subject data from embedded script
    const dataScript = document.getElementById('allSubjectsData');
    let allSubjects = [];
    if (dataScript) {
        try {
            allSubjects = JSON.parse(dataScript.textContent);
        } catch (e) {
            console.error('Failed to parse allSubjectsData', e);
        }
    }

    function filterSubjects() {
        const year = yearSelect.value;
        const sem  = semSelect.value;
        subjectDrop.innerHTML = '<option value="">-- Select Subject --</option>';
        if (!year || !sem) return;

        allSubjects.forEach(sub => {
            if (sub.year_of_study == year && sub.semester == sem) {
                const opt = document.createElement('option');
                opt.value = sub.subject_id;
                opt.textContent = sub.subject_code + ' - ' + sub.subject_name;
                subjectDrop.appendChild(opt);
            }
        });
    }

    yearSelect.addEventListener('change', filterSubjects);
    semSelect.addEventListener('change', filterSubjects);

    // If a subject is already selected (editing mode), trigger filter immediately
    if (subjectDrop.dataset.selected) {
        filterSubjects();
    }
}

/* -------------------------------------------------------
   5. AUTO‑DISMISS ALERTS
   ------------------------------------------------------- */
function setupAlertDismiss() {
    document.querySelectorAll('.alert-dismissible').forEach(alert => {
        setTimeout(() => {
            if (alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }
        }, 5000);
    });
}

/* -------------------------------------------------------
   6. FORM VALIDATION (Standard HTML5)
   ------------------------------------------------------- */
function setupFormValidations() {
    const forms = document.querySelectorAll('form');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            // No Bootstrap styling class needed
        }, false);
    });
}