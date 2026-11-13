document.addEventListener("DOMContentLoaded", function () {

    // ============================================================
    // PRODUCT IMAGES — Accumulate files across multiple selections
    // ============================================================
    const productInput = document.getElementById("product_images");
    const productPreviewWrapper = document.getElementById("image-preview-wrapper");
    const productUploadButtonWrapper = document.getElementById("upload-button-wrapper");

    // DataTransfer holds accumulated files (survives across multiple input changes)
    let productFilesDataTransfer = new DataTransfer();

    if (productInput) {
        productInput.addEventListener("change", function () {
            const newFiles = [...this.files];
            newFiles.forEach(function (file) {
                if (!file.type.startsWith("image/")) return;

                // Add file to accumulator
                productFilesDataTransfer.items.add(file);
                const fileIndex = productFilesDataTransfer.files.length - 1;

                const reader = new FileReader();
                reader.onload = function (e) {
                    const box = document.createElement("div");
                    box.classList.add("col-auto");
                    box.setAttribute("data-file-index", fileIndex);
                    box.innerHTML = `
                        <div class="preview-box">
                            <img src="${e.target.result}" alt="">
                            <button type="button" class="remove-btn" data-type="new">&times;</button>
                        </div>
                    `;
                    // Insert before upload button so it stays last
                    productPreviewWrapper.insertBefore(box, productUploadButtonWrapper);
                };
                reader.readAsDataURL(file);
            });

            // Sync accumulated files back into the input
            this.files = productFilesDataTransfer.files;
        });
    }

    // Handle remove clicks for product images (both existing and new)
    if (productPreviewWrapper) {
        productPreviewWrapper.addEventListener("click", function (e) {
            if (e.target.classList.contains("remove-btn")) {
                const box = e.target.closest(".col-auto");
                const removeType = e.target.getAttribute("data-type");

                if (removeType === "new") {
                    // Removing a newly selected (not yet uploaded) file
                    rebuildProductFiles(box);
                } else {
                    // Removing an existing (already uploaded) image — remove hidden input
                    const hiddenInput = box.querySelector('input[name="existing_images[]"]');
                    if (hiddenInput) hiddenInput.remove();
                }

                box.remove();
            }
        });
    }

    /**
     * Rebuild the DataTransfer after removing a new file preview.
     * Re-indexes all remaining new-file previews.
     */
    function rebuildProductFiles(removedBox) {
        const removedIndex = parseInt(removedBox.getAttribute("data-file-index"), 10);

        // Build a new DataTransfer without the removed file
        const newDT = new DataTransfer();
        const oldFiles = productFilesDataTransfer.files;

        for (let i = 0; i < oldFiles.length; i++) {
            if (i !== removedIndex) {
                newDT.items.add(oldFiles[i]);
            }
        }

        productFilesDataTransfer = newDT;

        // Re-index remaining new-file preview boxes
        const newBoxes = productPreviewWrapper.querySelectorAll('[data-file-index]');
        let newIdx = 0;
        newBoxes.forEach(function (box) {
            const idx = parseInt(box.getAttribute("data-file-index"), 10);
            if (idx !== removedIndex) {
                box.setAttribute("data-file-index", newIdx);
                newIdx++;
            }
        });

        // Sync back to input
        if (productInput) {
            productInput.files = productFilesDataTransfer.files;
        }
    }

    // ============================================================
    // DESCRIPTION IMAGES — Same accumulation pattern
    // ============================================================
    const descInput = document.getElementById("desc_images");
    const descPreviewWrapper = document.getElementById("desc-image-preview-wrapper");
    const descUploadButtonWrapper = document.getElementById("desc-upload-button-wrapper");

    let descFilesDataTransfer = new DataTransfer();

    if (descInput) {
        descInput.addEventListener("change", function () {
            const newFiles = [...this.files];
            newFiles.forEach(function (file) {
                if (!file.type.startsWith("image/")) return;

                descFilesDataTransfer.items.add(file);
                const fileIndex = descFilesDataTransfer.files.length - 1;

                const reader = new FileReader();
                reader.onload = function (e) {
                    const box = document.createElement("div");
                    box.classList.add("col-auto");
                    box.setAttribute("data-desc-file-index", fileIndex);
                    box.innerHTML = `
                        <div class="preview-box">
                            <img src="${e.target.result}" alt="">
                            <button type="button" class="remove-btn2" data-type="new">&times;</button>
                        </div>
                    `;
                    descPreviewWrapper.insertBefore(box, descUploadButtonWrapper);
                };
                reader.readAsDataURL(file);
            });

            // Sync accumulated files back into the input
            this.files = descFilesDataTransfer.files;
        });
    }

    // Handle remove clicks for description images
    if (descPreviewWrapper) {
        descPreviewWrapper.addEventListener("click", function (e) {
            if (e.target.classList.contains("remove-btn2")) {
                const box = e.target.closest(".col-auto");
                const removeType = e.target.getAttribute("data-type");

                if (removeType === "new") {
                    rebuildDescFiles(box);
                } else {
                    const hiddenInput = box.querySelector('input[name="existing_images_desc[]"]');
                    if (hiddenInput) hiddenInput.remove();
                }

                box.remove();
            }
        });
    }

    /**
     * Rebuild the description DataTransfer after removing a new file preview.
     */
    function rebuildDescFiles(removedBox) {
        const removedIndex = parseInt(removedBox.getAttribute("data-desc-file-index"), 10);

        const newDT = new DataTransfer();
        const oldFiles = descFilesDataTransfer.files;

        for (let i = 0; i < oldFiles.length; i++) {
            if (i !== removedIndex) {
                newDT.items.add(oldFiles[i]);
            }
        }

        descFilesDataTransfer = newDT;

        // Re-index remaining new-file preview boxes
        const newBoxes = descPreviewWrapper.querySelectorAll('[data-desc-file-index]');
        let newIdx = 0;
        newBoxes.forEach(function (box) {
            const idx = parseInt(box.getAttribute("data-desc-file-index"), 10);
            if (idx !== removedIndex) {
                box.setAttribute("data-desc-file-index", newIdx);
                newIdx++;
            }
        });

        if (descInput) {
            descInput.files = descFilesDataTransfer.files;
        }
    }

    // ============================================================
    // FORM SUBMIT — Ensure accumulated files are synced to inputs
    // ============================================================
    const form = document.getElementById("productForm");
    if (form) {
        form.addEventListener("submit", function () {
            // Final sync of accumulated files into the file inputs
            if (productInput && productFilesDataTransfer.files.length > 0) {
                productInput.files = productFilesDataTransfer.files;
            }
            if (descInput && descFilesDataTransfer.files.length > 0) {
                descInput.files = descFilesDataTransfer.files;
            }
        }, true); // Use capture phase to run BEFORE other submit handlers
    }
});
