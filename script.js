document.addEventListener("DOMContentLoaded", () => {

    /* --------------------
       DARK MODE TOGGLE
    -------------------- */
    if (localStorage.getItem("theme") === "dark") {
        document.body.classList.add("dark-mode");
    } else {
        document.body.classList.remove("dark-mode");
    }

    const themeCheckbox = document.getElementById("theme-switch-checkbox");
    if (themeCheckbox) {
        themeCheckbox.checked = localStorage.getItem("theme") === "dark";

        themeCheckbox.addEventListener("change", () => {
            if (themeCheckbox.checked) {
                document.body.classList.add("dark-mode");
                localStorage.setItem("theme", "dark");
            } else {
                document.body.classList.remove("dark-mode");
                localStorage.setItem("theme", "light");
            }
        });
    }

    const darkToggleBtn = document.getElementById("dark-mode-toggle");
    if (darkToggleBtn) {
        darkToggleBtn.addEventListener("click", () => {
            document.body.classList.toggle("dark-mode");
            const theme = document.body.classList.contains("dark-mode") ? "dark" : "light";
            localStorage.setItem("theme", theme);
        });
    }

    /* --------------------
       MODAL LOGIC
    -------------------- */
function setupModal(openBtnId, modalId, formId = null) {
    const openBtn = document.getElementById(openBtnId);
    const modal = document.getElementById(modalId);
    if (!openBtn || !modal) return;

    const closeBtn = modal.querySelector(".close");
    const form = formId ? document.getElementById(formId) : null;

    openBtn.addEventListener("click", (e) => {
        e.preventDefault();
        if (form) form.reset();  // <-- Reset the form when modal opens
        modal.style.display = "block";
    });

    closeBtn.addEventListener("click", () => {
        modal.style.display = "none";
    });

    window.addEventListener("click", (e) => {
        if (e.target === modal) modal.style.display = "none";
    });
}

function formatDate(dateStr) {
    const d = new Date(dateStr);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0'); // months are 0-based
    const year = d.getFullYear();
    return `${day}-${month}-${year}`;
}
document.querySelectorAll('.edit-btn').forEach(button => {
    button.addEventListener('click', function () {
        const tr = this.closest('tr'); 

        document.getElementById('createdAt').textContent = "Izveidots: " + 
            new Date(tr.dataset.created_at).toLocaleString('lv-LV', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });

        document.getElementById('lastEditAt').textContent = tr.dataset.last_edit_at
            ? "Pēdējoreiz rediģēts: " + new Date(tr.dataset.last_edit_at).toLocaleString('lv-LV', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            })
            : "Nav rediģēts";
    });
});




// Pass the form id for the review modal
setupModal("openReviewModalBtn", "reviewModal", "reviewForm");


    setupModal("openPiedavajumiModal", "piedavajumiModal");
    setupModal("openAboutModal", "aboutModal");
    setupModal("openReviewModalBtn", "reviewModal");
    setupModal("edit-account-btn", "editAccountModal");

    /* --------------------
       EDIT ACCOUNT MODAL
    -------------------- */
    const modal = document.getElementById("editAccountModal");
    const editBtn = document.getElementById("edit-account-btn");
    if (modal && editBtn) {
        const closeSpan = modal.querySelector(".close");

        editBtn.onclick = (e) => {
            e.preventDefault();
            modal.style.display = "block";
        };

        closeSpan.onclick = () => { modal.style.display = "none"; };

        window.onclick = (event) => {
            if (event.target === modal) modal.style.display = "none";
        };

        const form = document.getElementById("editAccountForm");
        if (form) {
            form.addEventListener("submit", function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                fetch("login/update-account.php", { method: "POST", body: formData })
                    .then(res => res.json())
                    .then(data => {
                        const msg = document.getElementById("editAccountMsg");
                        msg.textContent = data.message;
                        if (data.success) setTimeout(() => { location.reload(); }, 1500);
                    });
            });
        }
    }


// ---------------- Send Reminder Email ----------------
// Send reminder (expiring soon)
document.addEventListener('click', async function (e) {
    if (e.target.closest('.mail-btn')) {
        const tr = e.target.closest('tr');
        const email = tr.dataset.email;
        const days_left = tr.dataset.days_left;
        const type = 'expiring';

        if (!confirm("Send subscription expiry reminder email?")) return;

        const formData = new FormData();
        formData.append('email', email);
        formData.append('type', type);
        formData.append('days_left', days_left);

        const response = await fetch('api/send_email.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        alert(result.success || result.error);
    }

    if (e.target.closest('.expired-btn')) {
        const tr = e.target.closest('tr');
        const email = tr.dataset.email;
        const days_left = tr.dataset.days_left;
        const type = 'expired';

        if (!confirm("Send subscription expired email?")) return;

        const formData = new FormData();
        formData.append('email', email);
        formData.append('type', type);
        formData.append('days_left', days_left);

        const response = await fetch('api/send_email.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        alert(result.success || result.error);
    }
});



document.querySelectorAll('.send-email-btn').forEach(btn => {
    btn.addEventListener('click', async e => {
        const tr = e.target.closest('tr');
        const email = tr.dataset.email;
        const days_left = tr.dataset.days_left; // if needed
        const type = tr.dataset.days_left && [1,2,3].includes(+tr.dataset.days_left) ? 'expiring' : 'expired';

        if(!confirm("Send email to user?")) return;

        const formData = new FormData();
        formData.append('email', email);
        formData.append('type', type);
        formData.append('days_left', days_left);

        const response = await fetch('api/send_email.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        alert(result.success || result.error);
    });
});
async function loadReviews() {
    const container = document.querySelector(".reviews-container");
    if (!container) return;

    try {
        const response = await fetch("get_reviews.php"); // create this PHP file
        const reviews = await response.json();

        container.innerHTML = ""; // clear current reviews

        reviews.forEach(r => {
            const div = document.createElement("div");
            div.classList.add("review-box");
            div.innerHTML = `
                <h3>${r.name}</h3>
                <p>${r.review}</p>
                <p>${"★".repeat(r.stars) + "☆".repeat(5 - r.stars)}</p>
            `;
            container.appendChild(div);
        });
    } catch (err) {
        console.error("Failed to load reviews:", err);
    }
}


document.getElementById("reviewForm").addEventListener("submit", async function(e) {
    e.preventDefault(); // stop normal page reload

    const formData = new FormData(this);

    const response = await fetch("submit_review.php", {
        method: "POST",
        body: formData
    });

    const result = await response.json();

    if (result.success) {
        document.getElementById("editAccountMsg").textContent = result.message;
        
        // Reload reviews dynamically
        loadReviews();  

        // Close modal
        document.querySelector("#reviewModal").style.display = "none";
    } else {
        alert(result.message);
    }
});

document.addEventListener("DOMContentLoaded", () => {
    const dropdown = document.querySelector(".dropdown");
    const dropdownBtn = dropdown.querySelector("#sveiki-btn");
    const dropdownContent = dropdown.querySelector(".dropdown-content");

    // Toggle dropdown on button click
    dropdownBtn.addEventListener("click", (e) => {
        e.stopPropagation(); // Prevent event from bubbling to document
        dropdownContent.classList.toggle("show");
    });

    // Close dropdown if clicked outside
    document.addEventListener("click", () => {
        dropdownContent.classList.remove("show");
    });

    // Close dropdown on scroll
    window.addEventListener("scroll", () => {
        dropdownContent.classList.remove("show");
    });
});



    /* --------------------
       NAV ACTIVE ON SCROLL (Scrollspy)
    -------------------- */
    const sections = document.querySelectorAll("#top, #plans, #pakalpojumi, #about, #reviews");
    const navLinks = document.querySelectorAll("nav .nav-link");

    function setActiveLink() {
        const scrollPos = window.scrollY + window.innerHeight / 3;

        if (window.scrollY < 100) {
            // At the very top, highlight "Sākums"
            navLinks.forEach(link => link.classList.remove("active"));
            const topLink = document.querySelector('nav .nav-link[href="#top"]');
            if (topLink) topLink.classList.add("active");
            return;
        }

        let current = "";
        sections.forEach(section => {
            if (scrollPos >= section.offsetTop) {
                current = section.getAttribute("id");
            }
        });

        navLinks.forEach(link => {
            link.classList.remove("active");
            if (link.getAttribute("href") === `#${current}`) {
                link.classList.add("active");
            }
        });
    }
    document.querySelectorAll('.admin-menu-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const dropdown = btn.nextElementSibling; // the .dropdown-content
        dropdown.classList.toggle('show');
    });
});

// Close dropdown when clicking outside
window.addEventListener('click', function(e) {
    document.querySelectorAll('.mobile-dropdown .dropdown-content').forEach(dropdown => {
        if (!dropdown.contains(e.target) && !dropdown.previousElementSibling.contains(e.target)) {
            dropdown.classList.remove('show');
        }
    });
});

// Close dropdown when scrolling
window.addEventListener('scroll', function() {
    document.querySelectorAll('.mobile-dropdown .dropdown-content').forEach(dropdown => {
        dropdown.classList.remove('show');
    });
});


    setActiveLink(); // run once on load
    window.addEventListener("scroll", setActiveLink);

    // Smooth scroll
    navLinks.forEach(link => {
        link.addEventListener("click", function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute("href"));
            if (target) {
                target.scrollIntoView({ behavior: "smooth" });
            }
        });
    });

});
