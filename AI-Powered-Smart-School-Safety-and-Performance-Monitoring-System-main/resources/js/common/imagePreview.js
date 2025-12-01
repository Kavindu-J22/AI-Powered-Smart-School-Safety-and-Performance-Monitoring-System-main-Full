// resources/js/setup/company.js
document.addEventListener("DOMContentLoaded", function () {
    const logoInput = document.getElementById("logo");
    const logoPreview = document.getElementById("logoPreview");
    const previewImage = document.getElementById("previewImage");
    const clearForm = document.getElementById("clearForm");
    let objectUrl = null; // Store the URL to revoke it later

    console.log(
        "Script loaded. logoInput:",
        logoInput,
        "logoPreview:",
        logoPreview,
        "previewImage:",
        previewImage
    );

    if (logoInput) {
        console.log("logoInput found:", logoInput);
        logoInput.addEventListener("change", function () {
            const file = this.files[0];
            console.log("File selected:", file);

            // Revoke previous URL if it exists
            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
            }

            if (file && file.type.startsWith("image/")) {
                objectUrl = URL.createObjectURL(file);
                previewImage.src = objectUrl;
                logoPreview.classList.remove("d-none");
            } else {
                clearPreview();
                if (file) alert("Please select an image file.");
            }
        });
    } else {
        console.error("logoInput not found");
    }

    window.clearPreview = function () {
        if (logoInput) logoInput.value = "";
        if (objectUrl) {
            URL.revokeObjectURL(objectUrl);
            objectUrl = null;
        }
        previewImage.src = "#";
        logoPreview.classList.add("d-none");
    };

    if (clearForm) {
        clearForm.addEventListener("click", function (e) {
            e.preventDefault();
            clearPreview();
            document.querySelector("form").reset();
        });
    } else {
        console.error("clearForm not found");
    }
});
