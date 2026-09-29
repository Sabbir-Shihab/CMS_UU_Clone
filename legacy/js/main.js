const IMG = "https://www.uttara.ac.bd";

const HEADER_HTML = `
<header>
  <div class="header-top">
    <div class="container">
      <div class="header-top-row">
        <div class="logo-box">
          <a href="index.html" aria-label="Uttara University's homepage">
            <img src="${IMG}/wp-content/themes/utheme/images/UULogo.webp" alt="Uttara University">
          </a>
        </div>
        <div>
          <div class="top-header">
            <form class="search-form" action="/search" method="get">
              <input class="input-search" type="search" name="s" placeholder="Search …" aria-label="Search">
              <button class="search_button" type="submit">Search</button>
            </form>
            <ul class="header-top-list">
              <li><a class="phone" href="tel:09640888272"><i class="fa-solid fa-phone"></i> Hunting Number: 096 40 UTTARA (888272)</a></li>
            </ul>
          </div>
          <div class="header-menu">
            <ul class="header-top-list quick-links">
              <li><a href="https://erp.uttara.ac.bd/tuition-fee-calculator" target="_blank" rel="noopener">Tuition Fee Calculator</a></li>
              <li><a class="admission-link" href="https://erp.uttara.ac.bd/online-admission" target="_blank" rel="noopener">Online Admission</a></li>
              <li class="has-menu top-drop">
                <a href="#">Result</a>
                <div class="dropdown-menu">
                  <a href="https://erp.uttara.ac.bd/online-result" target="_blank" rel="noopener">Running Students</a>
                  <a href="https://erp.uttara.ac.bd/result-archive" target="_blank" rel="noopener">Result Archive</a>
                </div>
              </li>
              <li><a href="https://library.uttara.ac.bd/" target="_blank" rel="noopener">Library</a></li>
              <li><a href="bus.html">UU Bus Service</a></li>
              <li><a href="news.html">Newsletter</a></li>
              <li><a href="https://erp.uttarauniversity.edu.bd/online-payment" target="_blank" rel="noopener">Online Payment</a></li>
              <li><a href="contact.html">Apostille</a></li>
              <li><a href="contact.html">All Forms</a></li>
              <li><a href="https://erp.uttara.ac.bd/convocation-registration" target="_blank" rel="noopener">Convocation Registration</a></li>
              <li><a class="erp-button" href="https://erp.uttara.ac.bd/" target="_blank" rel="noopener">MYUU ERP</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="scroll-header">
    <div class="container">
      <nav class="navbar" aria-label="Primary">
        <a href="index.html">
          <img class="logo-mobile" src="${IMG}/wp-content/uploads/2024/10/UU-New-logo-mobile-version.png" alt="Uttara University">
        </a>
        <button class="navbar-toggler" type="button" aria-label="Toggle navigation">
          <span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
        </button>
        <ul class="main-nav">
          <li><a data-nav="home" href="index.html">Home</a></li>
          <li class="has-menu">
            <a data-nav="about" href="about.html">About</a>
            <div class="dropdown-menu">
              <a href="about.html">About UU</a>
              <a href="mission.html">Mission &amp; Vision &amp; Motto</a>
              <a href="overview.html">Overview, Features &amp; Facilities</a>
              <a href="unique-features.html">Our Unique Features</a>
              <a href="glance.html">UU at a Glance</a>
              <a href="rankings.html">Accreditations, Memberships and Ranking</a>
              <a href="campus.html">Explore Our Campus</a>
              <a href="campus-life.html">Campus Life</a>
              <a href="sustainability.html">UU Sustainability</a>
              <a href="logo.html">UU Logo Resource &amp; Socials</a>
              <a href="bus.html">UU Bus Service</a>
            </div>
          </li>
          <li class="has-menu">
            <a data-nav="academics" href="academics.html">Academic</a>
            <div class="dropdown-menu">
              <a href="academics.html">Schools</a>
              <a href="undergraduate.html">Undergraduate Programs</a>
              <a href="graduate.html">Graduate Programs</a>
              <a href="professional.html">Professional Programs</a>
              <a href="research.html">Research &amp; Innovation</a>
              <a href="iqac.html">IQAC - Institutional Quality Assurance Cell</a>
              <a href="partnerships.html">Academic, Research, and International Partnerships</a>
              <a href="corporate.html">Corporate Linkages</a>
              <a href="academic-calendar.html">Academic Calendar</a>
              <a href="grading-system.html">Grading System</a>
            </div>
          </li>
          <li class="has-menu">
            <a href="departments.html">Department</a>
            <div class="dropdown-menu wide">
              <div class="dept-grid">
                <a href="department-of-bengali.html">Department of Bangla</a>
                <a href="department-of-business-administration.html">Department of Business Administration</a>
                <a href="department-of-civil-engineering.html">Department of Civil Engineering</a>
                <a href="department-of-cse.html">Department of Computer Science and Engineering</a>
                <a href="department-of-education.html">Department of Education</a>
                <a href="department-of-eee.html">Department of Electrical and Electronic Engineering</a>
                <a href="department-of-english.html">Department of English</a>
                <a href="department-of-fashion-design.html">Department of Fashion Design and Technology</a>
                <a href="department-of-islamic-studies.html">Department of Islamic Studies</a>
                <a href="department-of-law.html">Department of Law</a>
                <a href="department-of-mathematics.html">Department of Mathematics</a>
                <a href="department-of-physics.html">Department of Physics</a>
                <a href="department-of-physical-education.html">Department of Physical Education</a>
                <a href="department-of-textile-engineering.html">Department of Textile Engineering</a>
              </div>
            </div>
          </li>
          <li class="has-menu">
            <a data-nav="admission" href="admission.html">Admission</a>
            <div class="dropdown-menu">
              <a href="admission.html">Why Study at UU</a>
              <a href="online-admission.html">Online Admission</a>
              <a href="scholarship-policy.html">Fall 2026 Scholarship Policy</a>
              <a href="entry-requirements.html">Entry Requirements</a>
              <a href="admission-test.html">Admission Test</a>
              <a href="credit-transfer.html">Credit Transfer</a>
              <a href="admission-procedure.html">Admission Procedure</a>
              <a href="tuition-fees.html">Tuition &amp; Fees</a>
              <a href="tuition-fee-calculator.html">Tuition Fee Calculator</a>
              <a href="scholarship.html">Scholarship</a>
              <a href="founders-scholarship-policy.html">Founder's Scholarship Policy</a>
              <a href="m-azizur-rahman-inclusive-scholarship.html">M Azizur Rahman Inclusive Scholarship Scheme</a>
              <a href="student-support-loan.html">Financial Aid &amp; Study Support Loan</a>
              <a href="international-students.html">International Students</a>
              <a href="payment-guidelines.html">Payment Guidelines</a>
              <a href="office-of-admission.html">Office of Admission</a>
              <a href="office-of-admission.html">Staff Directory</a>
            </div>
          </li>
          <li class="has-menu">
            <a data-nav="admin" href="administration.html">Administration</a>
            <div class="dropdown-menu">
              <a href="office-of-the-founder.html">Office of the Founder</a>
              <a href="office-of-the-vice-chancellor.html">Office of the Vice-Chancellor</a>
              <a href="office-of-the-treasurer.html">Office of the Treasurer</a>
              <a href="administration.html#office-of-the-adviser">Office of the Advisor</a>
              <a href="office-of-the-registrar.html">Office of the Registrar</a>
              <a href="office-of-the-controller-of-examinations.html">Office of the Controller of Examinations</a>
              <a href="office-of-the-proctor.html">Office of the Proctor</a>
              <a href="office-of-finance-and-accounts.html">Office of Finance and Accounts</a>
              <a href="office-of-it.html">Office of IT</a>
              <a href="office-of-public-relations.html">Office of Public Relations</a>
              <a href="faculty-hr-office.html">Faculty &amp; HR Office</a>
              <a href="office-of-external-affairs.html">Office of External Affairs</a>
              <a href="office-of-international-affairs.html">Office of International Affairs</a>
              <a href="office-of-student-affairs.html">Office of Student Affairs</a>
              <a href="office-of-brand-communication.html">Office of Brand &amp; Communication</a>
              <a href="medical-emergency-center.html">Medical &amp; Emergency Center and Mental Health Unit</a>
              <a href="office-of-logistics-procurement.html">Office of Logistics &amp; Procurement</a>
              <a href="office-of-student-services-counselling.html">Office of Student Services &amp; Counselling</a>
              <a href="office-of-internal-affairs.html">Office of Internal Affairs</a>
              <a href="office-of-internal-audit.html">Office of Internal Audit</a>
              <a href="office-of-compliance-legal-affairs.html">Office of Compliance &amp; Legal Affairs</a>
              <a href="office-of-the-project-director.html">Office of the Project Director</a>
              <a href="office-of-transport.html">Office of Transport</a>
              <a href="institutional-ranking-cell.html">Institutional Ranking Cell</a>
            </div>
          </li>
          <li class="has-menu">
            <a href="teachers.html">Teachers</a>
            <div class="dropdown-menu">
              <a href="teachers.html">Faculty Members</a>
              <a href="teachers.html#visiting">Visiting Faculty Members</a>
              <a href="teachers.html#biography">Book of Biography</a>
            </div>
          </li>
          <li class="has-menu">
            <a href="research-uu.html">Journal &amp; Publication</a>
            <div class="dropdown-menu">
              <a href="research-uu.html">Research @ UU</a>
              <a href="journals.html">Journals &amp; Other Publications</a>
            </div>
          </li>
          <li class="has-menu">
            <a href="gallery.html">Gallery</a>
            <div class="dropdown-menu">
              <a href="gallery.html">Video Gallery</a>
              <a href="convocation.html">Convocations</a>
            </div>
          </li>
          <li class="has-menu">
            <a href="alumni.html">Alumni</a>
            <div class="dropdown-menu">
              <a href="https://erp.uttara.ac.bd/alumni-login/" target="_blank" rel="noopener">Login / Registration</a>
              <a href="alumni.html">Get Involved</a>
              <a href="employment-records.html">Employment Records</a>
            </div>
          </li>
          <li class="has-menu">
            <a href="career.html">Career</a>
            <div class="dropdown-menu">
              <a href="career.html">List of Job Opening</a>
              <a href="https://www.uttara.ac.bd/job-application-form/" target="_blank" rel="noopener">Job Application Form</a>
            </div>
          </li>
          <li class="has-menu">
            <a href="convocation.html">Convocation</a>
            <div class="dropdown-menu">
              <a href="convocation.html#c9">9th Convocation</a>
              <a href="convocation.html#c8">8th Convocation</a>
              <a href="convocation.html#c7">7th Convocation</a>
              <a href="convocation.html#c6">6th Convocation</a>
              <a href="convocation.html#c5">5th Convocation</a>
              <a href="convocation.html#c4">4th Convocation</a>
              <a href="convocation.html#c3">3rd Convocation</a>
              <a href="convocation.html#c2">2nd Convocation</a>
              <a href="convocation.html#c1">1st Convocation</a>
            </div>
          </li>
        </ul>
      </nav>
    </div>
  </div>
</header>
`;

const FOOTER_HTML = `
<footer class="footer-bg">
  <div class="container">
    <h3 class="footer-social-head">Connect With Us</h3>
    <ul class="footer-social">
      <li><a href="https://www.facebook.com/UttaraUniversity" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-square"></i></a></li>
      <li><a href="https://twitter.com/Uttara_Varsity" target="_blank" rel="noopener" aria-label="Twitter"><i class="fab fa-twitter"></i></a></li>
      <li><a href="https://www.youtube.com/c/UttaraUniversityofficial" target="_blank" rel="noopener" aria-label="YouTube"><i class="fab fa-youtube"></i></a></li>
      <li><a href="https://www.linkedin.com/school/uttarauniversitybd" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a></li>
      <li><a href="https://www.instagram.com/uttarauniversity" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a></li>
      <li><a href="https://www.tiktok.com/@uttarauniversity" target="_blank" rel="noopener" aria-label="TikTok"><i class="fab fa-tiktok"></i></a></li>
    </ul>
    <div class="footer-cols">
      <div>
        <h3 class="footer-link-title">Admission</h3>
        <ul class="footer-link">
          <li><a href="admission.html">Why Study at UU</a></li>
          <li><a href="entry-requirements.html">Entry Requirements</a></li>
          <li><a href="payment-guidelines.html">Payment Guidelines</a></li>
          <li><a href="tuition-fees.html">Tuition &amp; Fees</a></li>
        </ul>
      </div>
      <div>
        <h3 class="footer-link-title">About</h3>
        <ul class="footer-link">
          <li><a href="about.html">About UU</a></li>
          <li><a href="overview.html">Overview</a></li>
          <li><a href="mission.html">Mission, Vision &amp; Motto</a></li>
          <li><a href="unique-features.html">Our Unique Features</a></li>
        </ul>
      </div>
      <div>
        <h3 class="footer-link-title">Core Links</h3>
        <ul class="footer-link">
          <li><a href="https://erp.uttara.ac.bd/online-result" target="_blank" rel="noopener">Running Students</a></li>
          <li><a href="https://erp.uttara.ac.bd/result-archive" target="_blank" rel="noopener">Result Archive</a></li>
          <li><a href="https://library.uttara.ac.bd/" target="_blank" rel="noopener">Library</a></li>
          <li><a href="https://erp.uttara.ac.bd/" target="_blank" rel="noopener">MYUU ERP</a></li>
        </ul>
      </div>
      <div>
        <h3 class="footer-link-title">Quick links</h3>
        <ul class="footer-link">
          <li><a href="contact.html">Contact Us</a></li>
          <li><a href="news.html">News &amp; Events</a></li>
          <li><a href="notices.html">Notice &amp; Announcements</a></li>
          <li><a href="contact.html">Privacy Policy</a></li>
        </ul>
      </div>
    </div>
    <p class="copyright">© 2026 Uttara University | Dhaka, Bangladesh. All Rights Reserved.</p>
  </div>
</footer>
<button class="to-top" type="button" aria-label="Back to top">↑</button>
`;

function cmsifyLinks(root) {
  if (!document.body || document.body.dataset.cms !== "1") return;
  (root || document).querySelectorAll("a[href]").forEach((a) => {
    const href = a.getAttribute("href");
    if (!href || /^(https?:|mailto:|tel:|#|\/)/i.test(href)) return;
    if (href.endsWith(".html")) {
      const name = href.replace(/\.html$/, "");
      a.setAttribute("href", name === "index" ? "/" : "/" + name);
    }
  });
}

function injectChrome() {
  if (document.body?.dataset.stack !== "1") {
    const header = document.querySelector("[data-header]");
    const footer = document.querySelector("[data-footer]");
    if (header) header.innerHTML = HEADER_HTML;
    if (footer) footer.innerHTML = FOOTER_HTML;
  }
  cmsifyLinks(document);
  const page = document.body.dataset.page;
  document.querySelectorAll(`[data-nav="${page}"]`).forEach((el) => el.classList.add("is-active"));
  const banner = document.querySelector(".inner-head .container");
  const crumb = document.querySelector(".page-wrap .crumb");
  if (banner && crumb) banner.appendChild(crumb);
}

function setupNav() {
  const toggle = document.querySelector(".navbar-toggler");
  const nav = document.querySelector(".main-nav");
  toggle?.addEventListener("click", () => nav.classList.toggle("is-open"));
  document.querySelectorAll(".has-menu > a").forEach((link) => {
    link.addEventListener("click", (event) => {
      if (window.innerWidth > 991) return;
      event.preventDefault();
      link.parentElement.classList.toggle("is-open");
    });
  });
}

function setupToTop() {
  const btn = document.querySelector(".to-top");
  if (!btn) return;
  window.addEventListener("scroll", () => btn.classList.toggle("is-visible", window.scrollY > 400), { passive: true });
  btn.addEventListener("click", () => window.scrollTo({ top: 0, behavior: "smooth" }));
}

function setupSlider() {
  document.querySelectorAll(".hero-slider").forEach((root) => {
    const slides = [...root.querySelectorAll(".slide")];
    const dotsWrap = root.querySelector(".slider-dots");
    if (!slides.length || !dotsWrap) return;
    let i = 0;
    slides.forEach((_, idx) => {
      const b = document.createElement("button");
      b.type = "button";
      b.setAttribute("aria-label", `Slide ${idx + 1}`);
      if (idx === 0) b.classList.add("is-on");
      b.addEventListener("click", () => go(idx));
      dotsWrap.appendChild(b);
    });
    const dots = [...dotsWrap.children];
    const go = (n) => {
      i = (n + slides.length) % slides.length;
      slides.forEach((s, idx) => s.classList.toggle("is-on", idx === i));
      dots.forEach((d, idx) => d.classList.toggle("is-on", idx === i));
    };
    slides[0]?.classList.add("is-on");
    root.querySelector(".prev")?.addEventListener("click", () => go(i - 1));
    root.querySelector(".next")?.addEventListener("click", () => go(i + 1));
    setInterval(() => go(i + 1), 5000);
  });
}

function setupNewsPager() {
  const root = document.querySelector("[data-news]");
  if (!root) return;
  const pages = [...root.querySelectorAll(".news-page")];
  const pager = root.querySelector(".news-pager");
  let i = 0;
  pages.forEach((_, idx) => {
    const b = document.createElement("button");
    b.type = "button";
    b.textContent = String(idx + 1);
    if (idx === 0) b.classList.add("is-on");
    b.addEventListener("click", () => go(idx));
    pager.appendChild(b);
  });
  const dots = [...pager.children];
  const go = (n) => {
    i = n;
    pages.forEach((p, idx) => p.classList.toggle("is-on", idx === i));
    dots.forEach((d, idx) => d.classList.toggle("is-on", idx === i));
  };
}

function setupTabs() {
  document.querySelectorAll("[data-tabs]").forEach((root) => {
    const bar = root.querySelector(":scope > .dept-tab-bg .dept-tabs, :scope > .dept-tabs");
    const buttons = bar
      ? [...bar.querySelectorAll(":scope > [data-tab]")]
      : [...root.querySelectorAll(":scope > [data-tab]")];
    const panels = [...root.querySelectorAll(":scope > main [data-panel], :scope > .panel, :scope > main .container > [data-panel]")];
    const activate = (name) => {
      buttons.forEach((b) => b.classList.toggle("is-active", b.dataset.tab === name));
      panels.forEach((p) => p.classList.toggle("is-active", p.dataset.panel === name));
    };
    buttons.forEach((btn) => {
      btn.addEventListener("click", () => activate(btn.dataset.tab));
    });
    const hash = (location.hash || "").replace("#", "");
    if (hash && buttons.some((b) => b.dataset.tab === hash)) activate(hash);
  });
}

const FACULTY_DEPTS = [
  { key: "bangla", code: 5, name: "Department of Bangla", page: "department-of-bengali.html" },
  { key: "bba", code: 4, name: "Department of Business Administration", page: "department-of-business-administration.html" },
  { key: "civil", code: 6, name: "Department of Civil Engineering", page: "department-of-civil-engineering.html" },
  { key: "cse", code: 8, name: "Department of Computer Science and Engineering", page: "department-of-cse.html" },
  { key: "education", code: 9, name: "Department of Education", page: "department-of-education.html" },
  { key: "eee", code: 10, name: "Department of Electrical and Electronic Engineering", page: "department-of-eee.html" },
  { key: "english", code: 11, name: "Department of English", page: "department-of-english.html" },
  { key: "fashion", code: 13, name: "Department of Fashion Design and Technology", page: "department-of-fashion-design.html" },
  { key: "islamic", code: 14, name: "Department of Islamic Studies", page: "department-of-islamic-studies.html" },
  { key: "law", code: 16, name: "Department of Law", page: "department-of-law.html" },
  { key: "math", code: 18, name: "Department of Mathematics", page: "department-of-mathematics.html" },
  { key: "physics", code: 20, name: "Department of Physics", page: "department-of-physics.html" },
  { key: "pe", code: 19, name: "Department of Physical Education", page: "department-of-physical-education.html" },
  { key: "textile", code: 23, name: "Department of Textile Engineering", page: "department-of-textile-engineering.html" },
];
const FACULTY_SKIP = new Set(["120046", "250069", "170604"]);

function escHtml(value) {
  return String(value || "").replace(/[&<>"']/g, (ch) => ({
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#39;",
  }[ch]));
}

function initialsFromName(name) {
  return (name || "")
    .split(/\s+/)
    .filter((part) => part && !/^(dr|prof|professor|md|md\.)$/i.test(part.replace(/\./g, "")))
    .slice(0, 2)
    .map((part) => part[0])
    .join("")
    .toUpperCase() || "UU";
}

function officeOfficialsLabel() {
  const title = (document.querySelector(".inner-head h1") || {}).textContent || "this office";
  const text = title.trim();
  if (/advisory board/i.test(text)) return "Officials of the Advisors";
  if (/vice chancellor/i.test(text)) return "Officials of Vice Chancellor";
  if (/founder/i.test(text)) return "Officials of the Founder";
  if (/registrar/i.test(text)) return "Officials of the Registrar";
  return `Officials of ${text.replace(/^Office of (the )?/i, "")}`;
}

function teacherCard(person) {
  const name = (person.EmpName || "").trim();
  const img = (person.EmpIMG || "").trim();
  const hasPhoto = img.startsWith("data:image") || /^https?:\/\//i.test(img);
  const photo = hasPhoto
    ? `<img src="${escHtml(img)}" alt="${escHtml(name)}" loading="lazy" onerror="this.outerHTML='<div class=&quot;teacher-photo-fallback&quot;>${escHtml(initialsFromName(name))}</div>'">`
    : `<div class="teacher-photo-fallback">${escHtml(initialsFromName(name))}</div>`;
  const role = (person.Designation || "").trim();
  const edu = [person.Institute1, person.Institute2]
    .map((line) => (line || "").trim())
    .filter(Boolean)
    .map((line) => `<p class="teacher-edu">${escHtml(line)}</p>`)
    .join("");
  const email = (person.email || "").trim();
  const ext = (person.office_ext || "").trim();
  const mail = email
    ? `<p><a class="teacher-mail" href="mailto:${escHtml(email)}"><i class="fa-solid fa-envelope"></i> ${escHtml(email)}</a></p>`
    : "";
  const phone = ext ? `<p class="teacher-ext"><i class="fa-solid fa-phone"></i> Ext: ${escHtml(ext)}</p>` : "";
  const profile = (person.EncryptedID || "").trim()
    ? `<a class="teacher-profile" href="https://www.uttara.ac.bd/employee-details?empid=${encodeURIComponent(person.EncryptedID)}" target="_blank" rel="noopener">View Profile</a>`
    : "";
  return `<article class="teacher-card">${photo}<div class="teacher-body"><h3>${escHtml(name)}</h3>${role ? `<p class="teacher-role">${escHtml(role)}</p>` : ""}${edu}${mail}${phone}${profile}</div></article>`;
}

async function loadFacultyList(code, mount) {
  if (!mount || mount.dataset.loaded === "1" || mount.dataset.loading === "1") return;
  mount.dataset.loading = "1";
    mount.innerHTML = `<p class="faculty-status">Loading directory from uttara.ac.bd…</p>`;
  try {
    const school = Boolean(mount.dataset.schoolCode);
    const endpoint = school
      ? `https://www.uttara.ac.bd/api/upload-school.php?action=schoolEmpList&schoolCode=${code}`
      : `https://www.uttara.ac.bd/api/upload.php?action=departmentEmpList&deptCode=${code}`;
    const response = await fetch(endpoint);
    if (!response.ok) throw new Error("Faculty request failed");
    const payload = await response.json();
    const people = (payload.data || []).filter((person) => !FACULTY_SKIP.has(String(person.EmpID || "")));
    if (!people.length) {
      const label = (mount.dataset.facultyCode || mount.closest("[data-panel='staff']")) ? "staff members" : "faculty members";
      mount.innerHTML = `<p class="faculty-status">No ${label} are listed yet.</p>`;
    } else {
      const isOffice = Boolean(mount.dataset.facultyCode) || Boolean(mount.dataset.schoolCode) || mount.closest("[data-panel='staff']");
      const count = mount.closest("[data-panel='teachers']") || isOffice ? "" : `<p class="faculty-count">${people.length} faculty members</p>`;
      const officeHead = isOffice ? `<h2 class="officials-title">${escHtml(officeOfficialsLabel())}</h2>` : "";
      const gridClass = isOffice ? "teacher-grid office-staff" : "teacher-grid";
      mount.innerHTML = `${officeHead}${count}<div class="${gridClass}">${people.map(teacherCard).join("")}</div>`;
    }
    mount.dataset.loaded = "1";
  } catch (error) {
    mount.innerHTML = `<p class="faculty-status">Could not load faculty right now. <a href="https://www.uttara.ac.bd/faculty/" target="_blank" rel="noopener">Open the official directory</a>.</p>`;
    mount.dataset.loaded = "0";
  } finally {
    mount.dataset.loading = "0";
  }
}

function setupFaculty() {
  const loadMount = (mount) => {
    if (!mount) return;
    if (mount.dataset.schoolCode) return loadFacultyList(mount.dataset.schoolCode, mount);
    if (mount.dataset.facultyCode) return loadFacultyList(mount.dataset.facultyCode, mount);
    const dept = FACULTY_DEPTS.find((item) => item.key === mount.dataset.faculty);
    if (dept) return loadFacultyList(dept.code, mount);
  };

  const visible = (el) => {
    const panel = el.closest("[data-panel]");
    return !panel || panel.classList.contains("is-active");
  };

  document.querySelectorAll("[data-tab='teachers'], [data-tab='staff']").forEach((btn) => {
    btn.addEventListener("click", () => {
      const root = btn.closest("[data-tabs]");
      const mount = root && (root.querySelector("[data-school-code]") || root.querySelector("[data-faculty-code]") || root.querySelector("[data-faculty]"));
      if (mount) loadMount(mount);
    });
  });

  document.querySelectorAll("[data-load-faculty]").forEach((btn) => {
    btn.addEventListener("click", () => {
      const key = btn.dataset.loadFaculty;
      const mount = document.querySelector(`[data-faculty="${key}"]`);
      btn.hidden = true;
      loadMount(mount);
    });
  });

  document.querySelectorAll("[data-faculty][data-autoload='true'], [data-faculty-code], [data-school-code]").forEach((mount) => {
    if (visible(mount)) loadMount(mount);
  });
}

function setupContactForm() {
  const form = document.querySelector("#contact-form");
  if (!form) return;
  form.addEventListener("submit", (event) => {
    event.preventDefault();
    form.querySelector(".form-success").hidden = false;
    form.reset();
  });
}

function setupFeeFilter() {
  const select = document.querySelector("#fee-filter");
  if (!select) return;
  select.addEventListener("change", () => {
    document.querySelectorAll("[data-school]").forEach((row) => {
      row.hidden = select.value !== "all" && row.dataset.school !== select.value;
    });
  });
}

function setupSearch() {
  const box = document.querySelector("#search-results");
  if (!box) return;
  const q = (new URLSearchParams(location.search).get("s") || "").trim();
  const input = document.querySelector("#site-search-input");
  if (input && q) input.value = q;
  const queryEl = document.querySelector("#search-query");
  const catalog = [
    ["About UU", "about.html"],
    ["Mission Vision Motto", "mission.html"],
    ["Overview Features Facilities", "overview.html"],
    ["Our Unique Features", "unique-features.html"],
    ["UU at a Glance", "glance.html"],
    ["Rankings and Accreditations", "rankings.html"],
    ["Explore Our Campus", "campus.html"],
    ["Campus Life", "campus-life.html"],
    ["UU Sustainability", "sustainability.html"],
    ["UU Logo Resource and Socials", "logo.html"],
    ["UU Bus Service", "bus.html"],
    ["Schools", "academics.html"],
    ["Undergraduate Programs", "undergraduate.html"],
    ["Graduate Programs", "graduate.html"],
    ["Professional Programs", "professional.html"],
    ["Research and Innovation", "research.html"],
    ["Center for Research and Training", "center-for-research-and-training.html"],
    ["Fintech Research Group", "fintech-research-group.html"],
    ["Center for Sustainable Nanoparticle Synthesis", "centre-for-sustainable-nanoparticle-synthesis.html"],
    ["Center for Clean Energy Research", "center-for-clean-energy-research.html"],
    ["Center for Bioinformatics and Artificial Intelligence", "center-for-bioinformatics-and-artificial-intelligence.html"],
    ["IQAC", "iqac.html"],
    ["Academic Research and International Partnerships", "partnerships.html"],
    ["Corporate Linkages", "corporate.html"],
    ["Academic Calendar", "academic-calendar.html"],
    ["Grading System", "grading-system.html"],
    ["Research at UU", "research-uu.html"],
    ["Journals and Other Publications", "journals.html"],
    ["Employment Records", "employment-records.html"],
    ["Departments", "departments.html"],
    ["Department of CSE", "department-of-cse.html"],
    ["Department of EEE", "department-of-eee.html"],
    ["Department of English", "department-of-english.html"],
    ["Department of Law", "department-of-law.html"],
    ["Department of Bangla", "department-of-bengali.html"],
    ["Department of Business Administration", "department-of-business-administration.html"],
    ["Department of Civil Engineering", "department-of-civil-engineering.html"],
    ["Department of Textile Engineering", "department-of-textile-engineering.html"],
    ["Department of Fashion Design", "department-of-fashion-design.html"],
    ["Department of Education", "department-of-education.html"],
    ["Department of Mathematics", "department-of-mathematics.html"],
    ["Department of Physics", "department-of-physics.html"],
    ["Department of Islamic Studies", "department-of-islamic-studies.html"],
    ["Department of Physical Education", "department-of-physical-education.html"],
    ["Admission Fall 2026", "admission.html"],
    ["Why Study at UU", "admission.html"],
    ["Online Admission", "online-admission.html"],
    ["Fall 2026 Scholarship Policy", "scholarship-policy.html"],
    ["Entry Requirements", "entry-requirements.html"],
    ["Admission Test", "admission-test.html"],
    ["Credit Transfer", "credit-transfer.html"],
    ["Admission Procedure", "admission-procedure.html"],
    ["Tuition and Fees", "tuition-fees.html"],
    ["Tuition Fee Calculator", "tuition-fee-calculator.html"],
    ["Scholarship", "scholarship.html"],
    ["Founder's Scholarship Policy", "founders-scholarship-policy.html"],
    ["M Azizur Rahman Inclusive Scholarship", "m-azizur-rahman-inclusive-scholarship.html"],
    ["Student Support Loan", "student-support-loan.html"],
    ["International Students", "international-students.html"],
    ["Payment Guidelines", "payment-guidelines.html"],
    ["Office of Admission", "office-of-admission.html"],
    ["Administration", "administration.html"],
    ["Office of the Founder", "office-of-the-founder.html"],
    ["Office of the Vice-Chancellor", "office-of-the-vice-chancellor.html"],
    ["Office of the Treasurer", "office-of-the-treasurer.html"],
    ["Office of the Advisor", "administration.html"],
    ["Office of the Registrar", "office-of-the-registrar.html"],
    ["Office of the Controller of Examinations", "office-of-the-controller-of-examinations.html"],
    ["Office of the Proctor", "office-of-the-proctor.html"],
    ["Office of Finance and Accounts", "office-of-finance-and-accounts.html"],
    ["Office of IT", "office-of-it.html"],
    ["Office of Public Relations", "office-of-public-relations.html"],
    ["Faculty and HR Office", "faculty-hr-office.html"],
    ["Office of External Affairs", "office-of-external-affairs.html"],
    ["Office of International Affairs", "office-of-international-affairs.html"],
    ["Office of Student Affairs", "office-of-student-affairs.html"],
    ["Office of Brand and Communication", "office-of-brand-communication.html"],
    ["Medical and Emergency Center", "medical-emergency-center.html"],
    ["Office of Logistics and Procurement", "office-of-logistics-procurement.html"],
    ["Office of Student Services and Counselling", "office-of-student-services-counselling.html"],
    ["Office of Internal Affairs", "office-of-internal-affairs.html"],
    ["Office of Internal Audit", "office-of-internal-audit.html"],
    ["Office of Compliance and Legal Affairs", "office-of-compliance-legal-affairs.html"],
    ["Office of the Project Director", "office-of-the-project-director.html"],
    ["Office of Transport", "office-of-transport.html"],
    ["Institutional Ranking Cell", "institutional-ranking-cell.html"],
    ["Teachers / Faculty", "teachers.html"],
    ["Gallery", "gallery.html"],
    ["Alumni", "alumni.html"],
    ["Career / Jobs", "career.html"],
    ["Convocation", "convocation.html"],
    ["News and Events", "news.html"],
    ["Notice and Announcements", "notices.html"],
    ["Contact Us", "contact.html"],
  ];
  const term = q.toLowerCase();
  const hits = term ? catalog.filter(([title]) => title.toLowerCase().includes(term)) : catalog;
  if (queryEl) queryEl.textContent = q ? `Results for “${q}”` : "Browse pages";
  box.innerHTML = hits.length
    ? hits.map(([title, href]) => `<li><a class="news-list-title" href="${href}">${title}</a></li>`).join("")
    : "<li>No results found.</li>";
}

function setupIqacFilters() {
  const bar = document.querySelector("[data-iqac-filters]");
  if (!bar) return;
  const buttons = [...bar.querySelectorAll("button")];
  const items = [...document.querySelectorAll("[data-iqac-cat]")];
  buttons.forEach((btn) => {
    btn.addEventListener("click", () => {
      const cat = btn.dataset.filter;
      buttons.forEach((b) => b.classList.toggle("is-active", b === btn));
      items.forEach((item) => {
        const show = cat === "all" || item.getAttribute("data-iqac-cat") === cat;
        item.style.display = show ? "flex" : "none";
      });
    });
  });
}

function setupActivitySlider() {
  const box = document.querySelector("[data-activity-slider]");
  if (!box) return;
  const slides = [...box.querySelectorAll("img")];
  if (slides.length < 2) return;
  let i = 0;
  setInterval(() => {
    slides[i].classList.remove("is-on");
    i = (i + 1) % slides.length;
    slides[i].classList.add("is-on");
  }, 3000);
}

document.addEventListener("DOMContentLoaded", () => {
  injectChrome();
  setupNav();
  setupToTop();
  setupSlider();
  setupNewsPager();
  setupTabs();
  setupFaculty();
  setupIqacFilters();
  setupActivitySlider();
  setupContactForm();
  setupFeeFilter();
  setupSearch();
});
