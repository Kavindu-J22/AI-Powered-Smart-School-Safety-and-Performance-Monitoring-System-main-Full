// Student Form JavaScript
let parentCount = 0;
let studentFormData = null;
let nfcModalInstance = null;
let currentSubjectData = null;
let selectedSubjects = {
    firstLanguage: null,
    religion: null,
    aesthetic: null,
    electives: [],
    stream: null,
    streamSubjects: [],
    core: [],
};

// Global function declarations - accessible from onclick handlers
window.addParentForm = function () {
    parentCount++;
    const container = document.getElementById("parentContainer");

    if (!container) {
        console.error("Parent container not found");
        alert("Error: Parent container not found");
        return;
    }

    const parentForm = `
        <div class="card border mb-3 parent-form-border" id="parentForm${parentCount}">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">New Parent ${parentCount}</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeParentForm(${parentCount})">
                        <i class="material-symbols-rounded">delete</i> Remove
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="input-group input-group-outline mb-3">
                            <label class="form-label">First Name *</label>
                            <input type="text" name="parent_first_name[]" class="form-control" required maxlength="50">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group input-group-outline mb-3">
                            <label class="form-label">Middle Name</label>
                            <input type="text" name="parent_middle_name[]" class="form-control" maxlength="50">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group input-group-outline mb-3">
                            <label class="form-label">Last Name *</label>
                            <input type="text" name="parent_last_name[]" class="form-control" required maxlength="50">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group input-group-outline mb-3">
                            <select name="parent_gender[]" class="form-control" required>
                                <option value="">Select Gender</option>
                                <option value="M">Male</option>
                                <option value="F">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="input-group input-group-outline mb-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="parent_date_of_birth[]" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group input-group-outline mb-3">
                            <select name="parent_relationship_type[]" class="form-control" required>
                                <option value="">Select Relationship</option>
                                <option value="Father">Father</option>
                                <option value="Mother">Mother</option>
                                <option value="Guardian">Guardian</option>
                                <option value="Stepfather">Stepfather</option>
                                <option value="Stepmother">Stepmother</option>
                                <option value="Grandfather">Grandfather</option>
                                <option value="Grandmother">Grandmother</option>
                                <option value="Uncle">Uncle</option>
                                <option value="Aunt">Aunt</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group input-group-outline mb-3">
                            <label class="form-label">Mobile Phone *</label>
                            <input type="text" name="parent_mobile_phone[]" class="form-control" required maxlength="15">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group input-group-outline mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="parent_email[]" class="form-control" maxlength="100">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="input-group input-group-outline mb-3">
                            <label class="form-label">Occupation</label>
                            <input type="text" name="parent_occupation[]" class="form-control" maxlength="100">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group input-group-outline mb-3">
                            <label class="form-label">Workplace</label>
                            <input type="text" name="parent_workplace[]" class="form-control" maxlength="100">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group input-group-outline mb-3">
                            <label class="form-label">Work Phone</label>
                            <input type="text" name="parent_work_phone[]" class="form-control" maxlength="15">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check form-switch pt-3">
                            <input class="form-check-input" type="checkbox" name="parent_is_emergency_contact[]" value="1">
                            <label class="form-check-label">Emergency Contact</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="input-group input-group-outline mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="parent_address_line1[]" class="form-control" rows="2" maxlength="255"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    container.insertAdjacentHTML("beforeend", parentForm);

    // Add animation to newly added form
    const newForm = document.getElementById(`parentForm${parentCount}`);
    if (newForm) {
        newForm.style.opacity = "0";
        newForm.style.transform = "translateY(20px)";
        setTimeout(() => {
            newForm.style.transition = "all 0.3s ease";
            newForm.style.opacity = "1";
            newForm.style.transform = "translateY(0)";
        }, 10);
    }
};

window.removeParentForm = function (parentId) {
    const parentForm = document.getElementById(`parentForm${parentId}`);
    if (!parentForm) {
        console.error("Parent form not found for removal");
        return;
    }

    // Add fade out animation
    parentForm.style.transition = "all 0.3s ease";
    parentForm.style.opacity = "0";
    parentForm.style.transform = "translateY(-20px)";

    setTimeout(() => {
        parentForm.remove();
    }, 300);
};

window.unlinkParent = function (parentId) {
    if (
        confirm("Are you sure you want to unlink this parent from the student?")
    ) {
        const parentElement = document.getElementById(
            `existingParent${parentId}`
        );
        if (parentElement) {
            // Add fade out animation
            parentElement.style.transition = "all 0.3s ease";
            parentElement.style.opacity = "0";
            parentElement.style.transform = "translateY(-20px)";

            setTimeout(() => {
                parentElement.remove();

                // Remove from existing_parents input array
                const existingParentsInputs = document.querySelectorAll(
                    'input[name="existing_parents[]"]'
                );
                existingParentsInputs.forEach((input) => {
                    if (input.value == parentId) {
                        input.remove();
                    }
                });
            }, 300);
        }
    }
};

window.toggleParentSelector = function () {
    const selector = document.getElementById("parentSelector");
    if (selector) {
        if (selector.style.display === "none") {
            selector.style.display = "block";
            selector.style.opacity = "0";
            setTimeout(() => {
                selector.style.transition = "all 0.3s ease";
                selector.style.opacity = "1";
            }, 10);
        } else {
            selector.style.transition = "all 0.3s ease";
            selector.style.opacity = "0";
            setTimeout(() => {
                selector.style.display = "none";
            }, 300);
        }
    }
};

window.generateStudentCode = function () {
    const studentCodeInput = document.querySelector(
        'input[name="student_code"]'
    );

    // This will be set by the blade template
    const generateCodeUrl =
        window.generateCodeUrl || "/admin/management/students/generate-code";

    fetch(generateCodeUrl)
        .then((response) => response.json())
        .then((data) => {
            if (data.code) {
                studentCodeInput.value = data.code;
                // Mark field as filled for Material Design
                studentCodeInput
                    .closest(".input-group")
                    .classList.add("is-filled");
            }
        })
        .catch((error) => {
            console.error("Error fetching code:", error);
            // Fallback: generate a temporary code
            const timestamp = Date.now();
            studentCodeInput.value =
                "stu-" + String(timestamp).slice(-8).padStart(8, "0");
            studentCodeInput.closest(".input-group").classList.add("is-filled");
        });
};

window.resetForm = function () {
    // Reset parent forms
    document.getElementById("parentContainer").innerHTML = "";
    parentCount = 0;

    // Add one parent form back
    const isEditMode = window.isEditMode || false;
    if (!isEditMode) {
        window.addParentForm();
        window.generateStudentCode();
    }

    // Reset Material Design form states
    document.querySelectorAll(".input-group-outline").forEach((group) => {
        group.classList.remove("is-filled", "is-focused");
    });
};

// NFC Modal functions
function showNFCModal() {
    const modal = document.getElementById("nfcModal");
    if (!modal) return;

    // Reset modal state
    document.getElementById("nfcWaiting").style.display = "block";
    document.getElementById("nfcSuccess").style.display = "none";
    document.getElementById("nfcError").style.display = "none";
    document.getElementById("nfcSkipBtn").style.display = "inline-block";
    document.getElementById("nfcCancelBtn").style.display = "inline-block";
    document.getElementById("nfcContinueBtn").style.display = "none";

    // Initialize Bootstrap modal if not already done
    if (!nfcModalInstance) {
        nfcModalInstance = new bootstrap.Modal(modal);
    }

    nfcModalInstance.show();

    // Start NFC writing process
    writeToNFC();
}

function hideNFCModal() {
    if (nfcModalInstance) {
        nfcModalInstance.hide();
    }
}

async function writeToNFC() {
    try {
        // Prepare student data to send to backend
        const studentData = {
            student_code:
                document.querySelector('input[name="student_code"]')?.value ||
                "",
            first_name:
                document.querySelector('input[name="first_name"]')?.value || "",
            last_name:
                document.querySelector('input[name="last_name"]')?.value || "",
            grade_level:
                document.querySelector('select[name="grade_level"]')?.value ||
                "",
            class_id:
                document.querySelector('select[name="class_id"]')?.value || "",
            enrollment_date:
                document.querySelector('input[name="enrollment_date"]')
                    ?.value || "",
        };

        // Get CSRF token
        const csrfToken =
            document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content") ||
            document.querySelector('input[name="_token"]')?.value;

        // Send AJAX request to backend to write NFC via Arduino
        const response = await fetch("/admin/management/students/write-nfc", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
                Accept: "application/json",
            },
            body: JSON.stringify(studentData),
        });

        const result = await response.json();

        if (result.success) {
            showNFCSuccess();
        } else {
            showNFCError(result.message || "Failed to write data to NFC tag.");
        }
    } catch (error) {
        console.error("NFC Write Error:", error);
        showNFCError(
            `Error communicating with server: ${error.message}. Please ensure the Arduino is connected.`
        );
    }
}

function showNFCSuccess() {
    document.getElementById("nfcWaiting").style.display = "none";
    document.getElementById("nfcSuccess").style.display = "block";
    document.getElementById("nfcError").style.display = "none";
    document.getElementById("nfcSkipBtn").style.display = "none";
    document.getElementById("nfcCancelBtn").style.display = "none";
    document.getElementById("nfcContinueBtn").style.display = "inline-block";
}

function showNFCError(message) {
    document.getElementById("nfcWaiting").style.display = "none";
    document.getElementById("nfcSuccess").style.display = "none";
    document.getElementById("nfcError").style.display = "block";
    document.getElementById("nfcErrorMessage").textContent = message;
    document.getElementById("nfcSkipBtn").style.display = "inline-block";
    document.getElementById("nfcCancelBtn").style.display = "inline-block";
}

function submitStudentFormDirectly() {
    const form = document.getElementById("studentForm");
    if (form) {
        // Remove the submit event listener temporarily to avoid loop
        form.removeEventListener("submit", handleFormSubmit);
        form.submit();
    }
}

function handleFormSubmit(event) {
    event.preventDefault();

    // Store the form for later submission
    studentFormData = event.target;

    // Validate form before submission
    if (!studentFormData.checkValidity()) {
        studentFormData.reportValidity();
        return;
    }

    // Validate subject selections
    if (!validateSubjectSelections()) {
        return;
    }

    // Submit the form directly (NFC functionality disabled)
    submitStudentFormDirectly();
}

// DOM Content Loaded event
document.addEventListener("DOMContentLoaded", function () {
    // Auto-generate student code if creating new student
    const studentCodeInput = document.querySelector(
        'input[name="student_code"]'
    );
    const isEditMode = window.isEditMode || false;

    if (!isEditMode && studentCodeInput && !studentCodeInput.value) {
        // Generate student code
        window.generateStudentCode();
    }

    // Add first parent form by default for new students
    if (!isEditMode) {
        window.addParentForm();
    }

    // Material Design form field handlers
    document.addEventListener(
        "focus",
        function (e) {
            if (e.target.matches(".form-control")) {
                e.target
                    .closest(".input-group-outline")
                    ?.classList.add("is-focused");
            }
        },
        true
    );

    document.addEventListener(
        "blur",
        function (e) {
            if (e.target.matches(".form-control")) {
                const group = e.target.closest(".input-group-outline");
                if (group) {
                    group.classList.remove("is-focused");
                    if (e.target.value) {
                        group.classList.add("is-filled");
                    } else {
                        group.classList.remove("is-filled");
                    }
                }
            }
        },
        true
    );

    // Profile Image Preview
    const profileImageInput = document.getElementById("profileImage");
    if (profileImageInput) {
        profileImageInput.addEventListener("change", function (e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const preview = document.getElementById("profilePreview");
                    if (preview) {
                        preview.innerHTML = `<img src="${e.target.result}" alt="Student Photo" class="w-100 border-radius-lg shadow-sm" style="height: 120px; object-fit: cover;">`;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Handle form submission - intercept to show NFC modal
    const studentForm = document.getElementById("studentForm");
    if (studentForm) {
        studentForm.addEventListener("submit", handleFormSubmit);
    }

    // NFC Modal button handlers
    const nfcSkipBtn = document.getElementById("nfcSkipBtn");
    if (nfcSkipBtn) {
        nfcSkipBtn.addEventListener("click", function () {
            hideNFCModal();
            submitStudentFormDirectly();
        });
    }

    const nfcCancelBtn = document.getElementById("nfcCancelBtn");
    if (nfcCancelBtn) {
        nfcCancelBtn.addEventListener("click", function () {
            hideNFCModal();
        });
    }

    const nfcContinueBtn = document.getElementById("nfcContinueBtn");
    if (nfcContinueBtn) {
        nfcContinueBtn.addEventListener("click", function () {
            hideNFCModal();
            submitStudentFormDirectly();
        });
    }

    // Dynamic Subject Loading based on Grade Level
    const gradeLevelSelect = document.querySelector(
        'select[name="grade_level"]'
    );
    const classSelect = document.querySelector('select[name="class_id"]');

    if (gradeLevelSelect) {
        // Load classes and subjects when grade is changed
        gradeLevelSelect.addEventListener("change", function () {
            const gradeLevel = this.value;
            loadClassesByGrade(gradeLevel);
            loadSubjectsByGrade(gradeLevel);
        });

        // Load on page load if grade is already selected
        if (gradeLevelSelect.value) {
            loadClassesByGrade(gradeLevelSelect.value);
            loadSubjectsByGrade(gradeLevelSelect.value);
        }
    }

    // Handle stream selection for Advanced Level
    document.querySelectorAll('input[name="stream"]').forEach((radio) => {
        radio.addEventListener("change", function () {
            if (this.checked) {
                handleStreamSelection(this.value);
            }
        });
    });
});

// Function to load classes by grade
function loadClassesByGrade(gradeLevel) {
    const classSelect = document.getElementById("class_id");

    if (!classSelect || !gradeLevel) {
        return;
    }

    // Filter classes from window.allClasses
    const filteredClasses = window.allClasses
        ? window.allClasses.filter((c) => c.grade_level == gradeLevel)
        : [];

    // Clear and repopulate
    classSelect.innerHTML = '<option value="">Select Class</option>';

    if (filteredClasses.length === 0) {
        classSelect.innerHTML =
            '<option value="">No classes available for this grade</option>';
    } else {
        filteredClasses.forEach((schoolClass) => {
            const option = document.createElement("option");
            option.value = schoolClass.id;
            option.textContent = `${schoolClass.class_name} (Grade ${schoolClass.grade_level})`;
            // Pre-select if editing and matches selectedClassId
            if (window.selectedClassId && window.selectedClassId == schoolClass.id) {
                option.selected = true;
            }
            classSelect.appendChild(option);
        });
    }
}

// Function to load subjects based on selected grade
function loadSubjectsByGrade(gradeLevel) {
    if (!gradeLevel) {
        document.getElementById("subjectSelectionContainer").style.display =
            "none";
        return;
    }

    // Get the URL from window object (set in blade template)
    const url =
        window.subjectsByGradeUrl ||
        "/admin/management/students/subjects-by-grade";

    // Get CSRF token
    const csrfToken =
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content") ||
        document.querySelector('input[name="_token"]')?.value;

    // Fetch subjects from server
    fetch(`${url}?grade_level=${gradeLevel}`, {
        method: "GET",
        headers: {
            Accept: "application/json",
            "X-CSRF-TOKEN": csrfToken,
        },
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success && data.data) {
                currentSubjectData = data.data;
                renderSubjectSelection(data.data);
            } else {
                console.error("Error loading subjects:", data.message);
                alert("Error loading subjects. Please try again.");
            }
        })
        .catch((error) => {
            console.error("Error fetching subjects:", error);
            alert("Error connecting to server. Please check your connection.");
        });
}

// Render subject selection based on education level
function renderSubjectSelection(data) {
    // Show container
    document.getElementById("subjectSelectionContainer").style.display =
        "block";

    // Hide all sections first
    document.getElementById("primarySubjects").style.display = "none";
    document.getElementById("secondarySubjects").style.display = "none";
    document.getElementById("advancedSubjects").style.display = "none";

    // Update info text
    document.getElementById(
        "educationLevelText"
    ).textContent = `${data.education_level} - Grade ${data.grade}`;

    // Reset selections
    resetSubjectSelections();

    // Render based on education level
    if (data.education_level === "Primary Education") {
        renderPrimarySubjects(data);
    } else if (data.education_level === "Secondary Education") {
        renderSecondarySubjects(data);
    } else if (data.education_level === "Advanced Level") {
        renderAdvancedSubjects(data);
    }
}

// Render Primary Education subjects
function renderPrimarySubjects(data) {
    document.getElementById("primarySubjects").style.display = "block";

    // First Language
    if (data.subjects.first_language) {
        renderCheckboxGroup(
            "firstLanguagePrimary",
            data.subjects.first_language,
            "radio",
            "first_language"
        );
    }

    // Religion
    if (data.subjects.religion) {
        renderCheckboxGroup(
            "religionPrimary",
            data.subjects.religion,
            "radio",
            "religion"
        );
    }

    // Aesthetic Studies
    if (data.subjects.aesthetic) {
        renderCheckboxGroup(
            "aestheticPrimary",
            data.subjects.aesthetic,
            "radio",
            "aesthetic"
        );
    }

    // Core subjects (display only)
    if (data.subjects.core) {
        const coreList = document.getElementById("corePrimaryList");
        coreList.innerHTML = "";
        data.subjects.core.forEach((subject) => {
            const li = document.createElement("li");
            li.textContent = subject.subject_name;
            coreList.appendChild(li);
            selectedSubjects.core.push(subject.id);
        });
    }
}

// Render Secondary Education subjects
function renderSecondarySubjects(data) {
    document.getElementById("secondarySubjects").style.display = "block";

    // First Language
    if (data.subjects.first_language) {
        renderCheckboxGroup(
            "firstLanguageSecondary",
            data.subjects.first_language,
            "radio",
            "first_language"
        );
    }

    // Religion
    if (data.subjects.religion) {
        renderCheckboxGroup(
            "religionSecondary",
            data.subjects.religion,
            "radio",
            "religion"
        );
    }

    // Core subjects (display only)
    if (data.subjects.core) {
        const coreList = document.getElementById("coreSecondaryList");
        coreList.innerHTML = "";
        data.subjects.core.forEach((subject) => {
            const li = document.createElement("li");
            li.textContent = subject.subject_name;
            coreList.appendChild(li);
            selectedSubjects.core.push(subject.id);
        });
    }

    // Elective subjects
    if (data.subjects.elective) {
        renderCheckboxGroup(
            "electiveSecondary",
            data.subjects.elective,
            "checkbox",
            "elective",
            3
        );
    }
}

// Render Advanced Level subjects
function renderAdvancedSubjects(data) {
    document.getElementById("advancedSubjects").style.display = "block";

    // Store stream subjects for later use
    window.streamSubjectsData = data.subjects.streams;
}

// Generic function to render checkbox/radio groups
function renderCheckboxGroup(
    containerId,
    subjects,
    inputType,
    category,
    maxSelection = 1
) {
    const container = document.getElementById(containerId);
    container.innerHTML = "";

    subjects.forEach((subject) => {
        const colDiv = document.createElement("div");
        colDiv.className = "col-md-4";

        const checkDiv = document.createElement("div");
        checkDiv.className = "subject-checkbox";

        const input = document.createElement("input");
        input.type = inputType;
        input.className = "form-check-input me-2";
        input.id = `subject_${subject.id}`;
        input.value = subject.id;
        input.name =
            inputType === "radio"
                ? `subject_${category}`
                : `subject_${category}[]`;
        input.dataset.category = category;
        input.dataset.maxSelection = maxSelection;

        const label = document.createElement("label");
        label.className = "form-check-label";
        label.htmlFor = `subject_${subject.id}`;
        label.textContent = subject.subject_name;

        checkDiv.appendChild(input);
        checkDiv.appendChild(label);
        colDiv.appendChild(checkDiv);
        container.appendChild(colDiv);

        // Add event listener
        input.addEventListener("change", function () {
            handleSubjectSelection(this, category, inputType, maxSelection);
            updateSubjectCheckboxStyles(this);
        });

        // Add click listener to div
        checkDiv.addEventListener("click", function (e) {
            if (e.target !== input) {
                input.click();
            }
        });
    });
}

// Handle subject selection
function handleSubjectSelection(input, category, inputType, maxSelection) {
    if (inputType === "radio") {
        // Radio button - single selection
        if (category === "first_language") {
            selectedSubjects.firstLanguage = input.checked
                ? parseInt(input.value)
                : null;
        } else if (category === "religion") {
            selectedSubjects.religion = input.checked
                ? parseInt(input.value)
                : null;
        } else if (category === "aesthetic") {
            selectedSubjects.aesthetic = input.checked
                ? parseInt(input.value)
                : null;
        }
    } else {
        // Checkbox - multiple selection with limit
        if (category === "elective") {
            if (input.checked) {
                if (selectedSubjects.electives.length < maxSelection) {
                    selectedSubjects.electives.push(parseInt(input.value));
                } else {
                    input.checked = false;
                    alert(
                        `You can only select ${maxSelection} elective subjects`
                    );
                    return;
                }
            } else {
                selectedSubjects.electives = selectedSubjects.electives.filter(
                    (id) => id !== parseInt(input.value)
                );
            }
            document.getElementById("electiveCount").textContent =
                selectedSubjects.electives.length;
        } else if (category === "stream_subject") {
            if (input.checked) {
                if (selectedSubjects.streamSubjects.length < maxSelection) {
                    selectedSubjects.streamSubjects.push(parseInt(input.value));
                } else {
                    input.checked = false;
                    alert(
                        `You can only select ${maxSelection} subjects from your stream`
                    );
                    return;
                }
            } else {
                selectedSubjects.streamSubjects =
                    selectedSubjects.streamSubjects.filter(
                        (id) => id !== parseInt(input.value)
                    );
            }
            document.getElementById("streamSubjectCount").textContent =
                selectedSubjects.streamSubjects.length;
        }
    }

    // Update hidden input
    updateSubjectHiddenInputs();
}

// Handle stream selection for Advanced Level
function handleStreamSelection(stream) {
    selectedSubjects.stream = stream;
    selectedSubjects.streamSubjects = [];

    // Update stream card styling
    document.querySelectorAll(".stream-card").forEach((card) => {
        card.classList.remove("active");
    });
    document
        .querySelector(`.stream-card[data-stream="${stream}"]`)
        ?.classList.add("active");

    // Show stream subjects
    const container = document.getElementById("streamSubjectsContainer");
    container.style.display = "block";

    // Load subjects for this stream
    const streamSubjects = window.streamSubjectsData?.[stream] || [];
    renderCheckboxGroup(
        "streamSubjects",
        streamSubjects,
        "checkbox",
        "stream_subject",
        3
    );
}

// Update checkbox visual styles
function updateSubjectCheckboxStyles(input) {
    const checkDiv = input.closest(".subject-checkbox");
    if (input.checked) {
        checkDiv.classList.add("selected");
    } else {
        checkDiv.classList.remove("selected");
    }
}

// Update hidden inputs with selected subjects
function updateSubjectHiddenInputs() {
    const allSubjects = [
        ...selectedSubjects.core,
        selectedSubjects.firstLanguage,
        selectedSubjects.religion,
        selectedSubjects.aesthetic,
        ...selectedSubjects.electives,
        ...selectedSubjects.streamSubjects,
    ].filter((id) => id !== null && id !== undefined);

    document.getElementById("subject_ids").value = JSON.stringify(allSubjects);
    document.getElementById("core_subject_ids").value = JSON.stringify(
        selectedSubjects.core
    );
}

// Validate subject selections before form submission
function validateSubjectSelections() {
    if (!currentSubjectData) {
        return true; // No validation needed if no subjects loaded
    }

    const educationLevel = currentSubjectData.education_level;
    let isValid = true;
    let errors = [];

    if (educationLevel === "Primary Education") {
        // Validate first language
        if (!selectedSubjects.firstLanguage) {
            document
                .getElementById("firstLanguagePrimaryError")
                .classList.remove("d-none");
            errors.push("Please select a first language");
            isValid = false;
        } else {
            document
                .getElementById("firstLanguagePrimaryError")
                .classList.add("d-none");
        }

        // Validate religion
        if (!selectedSubjects.religion) {
            document
                .getElementById("religionPrimaryError")
                .classList.remove("d-none");
            errors.push("Please select a religion");
            isValid = false;
        } else {
            document
                .getElementById("religionPrimaryError")
                .classList.add("d-none");
        }

        // Validate aesthetic
        if (!selectedSubjects.aesthetic) {
            document
                .getElementById("aestheticPrimaryError")
                .classList.remove("d-none");
            errors.push("Please select an aesthetic study");
            isValid = false;
        } else {
            document
                .getElementById("aestheticPrimaryError")
                .classList.add("d-none");
        }
    } else if (educationLevel === "Secondary Education") {
        // Validate first language
        if (!selectedSubjects.firstLanguage) {
            document
                .getElementById("firstLanguageSecondaryError")
                .classList.remove("d-none");
            errors.push("Please select a first language");
            isValid = false;
        } else {
            document
                .getElementById("firstLanguageSecondaryError")
                .classList.add("d-none");
        }

        // Validate religion
        if (!selectedSubjects.religion) {
            document
                .getElementById("religionSecondaryError")
                .classList.remove("d-none");
            errors.push("Please select a religion");
            isValid = false;
        } else {
            document
                .getElementById("religionSecondaryError")
                .classList.add("d-none");
        }

        // Validate electives (exactly 3)
        if (selectedSubjects.electives.length !== 3) {
            document
                .getElementById("electiveSecondaryError")
                .classList.remove("d-none");
            errors.push("Please select exactly 3 elective subjects");
            isValid = false;
        } else {
            document
                .getElementById("electiveSecondaryError")
                .classList.add("d-none");
        }
    } else if (educationLevel === "Advanced Level") {
        // Validate stream
        if (!selectedSubjects.stream) {
            document.getElementById("streamError").classList.remove("d-none");
            errors.push("Please select a stream");
            isValid = false;
        } else {
            document.getElementById("streamError").classList.add("d-none");
        }

        // Validate stream subjects (exactly 3)
        if (selectedSubjects.streamSubjects.length !== 3) {
            document
                .getElementById("streamSubjectsError")
                .classList.remove("d-none");
            errors.push(
                "Please select exactly 3 subjects from your chosen stream"
            );
            isValid = false;
        } else {
            document
                .getElementById("streamSubjectsError")
                .classList.add("d-none");
        }
    }

    if (!isValid) {
        alert(
            "Please complete all required subject selections:\n\n" +
                errors.join("\n")
        );
    }

    return isValid;
}

// Reset subject selections
function resetSubjectSelections() {
    selectedSubjects = {
        firstLanguage: null,
        religion: null,
        aesthetic: null,
        electives: [],
        stream: null,
        streamSubjects: [],
        core: [],
    };
    updateSubjectHiddenInputs();
}
