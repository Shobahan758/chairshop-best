const slugify = (value) => value
    .normalize('NFC')
    .toLocaleLowerCase()
    .trim()
    .replace(/[^\p{L}\p{M}\p{N}]+/gu, '-')
    .replace(/^-+|-+$/g, '');

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-tag-input]').forEach((source) => {
        const editor = document.createElement('input');
        editor.type = 'text';
        editor.className = 'product-tag-editor';
        editor.id = source.id;
        editor.placeholder = source.placeholder;
        editor.setAttribute('aria-describedby', `${source.id}Help`);
        source.removeAttribute('id');
        source.type = 'hidden';

        const tagsContainer = document.createElement('div');
        tagsContainer.className = 'product-tag-list';
        const control = document.createElement('div');
        control.className = 'form-control product-tag-control';
        control.classList.toggle('is-invalid', source.classList.contains('is-invalid'));
        control.append(tagsContainer, editor);
        source.before(control);
        control.addEventListener('click', (event) => {
            if (event.target === control) editor.focus();
        });
        let tags = [...new Set(source.value.split(',').map((tag) => tag.trim()).filter(Boolean))];

        const renderTags = () => {
            source.value = tags.join(', ');
            tagsContainer.replaceChildren();
            tags.forEach((tag, index) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'product-tag-chip';
                button.textContent = `${tag} ×`;
                button.setAttribute('aria-label', `Remove ${tag}`);
                button.addEventListener('click', () => {
                    tags.splice(index, 1);
                    renderTags();
                    editor.setCustomValidity('');
                    editor.focus();
                });
                tagsContainer.append(button);
            });
        };

        const addTags = () => {
            const nextTags = [...new Set([...tags, ...editor.value.split(',').map((tag) => tag.trim()).filter(Boolean)])];
            if (Array.from(nextTags.join(', ')).length > 255) {
                editor.setCustomValidity('Keep the combined tags within 255 characters.');
                editor.reportValidity();
                return;
            }
            tags = nextTags;
            editor.value = '';
            editor.setCustomValidity('');
            renderTags();
        };

        editor.addEventListener('input', () => editor.setCustomValidity(''));
        editor.addEventListener('keydown', (event) => {
            if (!event.isComposing && (event.key === 'Enter' || event.key === ',')) {
                event.preventDefault();
                addTags();
            }
        });
        editor.addEventListener('blur', addTags);
        source.form?.addEventListener('submit', (event) => {
            addTags();
            if (!editor.checkValidity()) event.preventDefault();
        });
        renderTags();
    });

    const categorySelect = document.querySelector('[data-category-select]');
    const subcategorySelect = document.querySelector('[data-subcategory-select]');

    if (categorySelect && subcategorySelect) {
        const filterSubcategories = () => {
            const categoryId = categorySelect.value;

            Array.from(subcategorySelect.options).forEach((option) => {
                if (! option.dataset.categoryId) return;

                option.hidden = option.dataset.categoryId !== categoryId;
                option.disabled = option.hidden;
            });

            if (subcategorySelect.selectedOptions[0]?.disabled) {
                subcategorySelect.value = '';
            }
        };

        categorySelect.addEventListener('change', filterSubcategories);
        filterSubcategories();
    }

    const skuInput = document.querySelector('[data-sku-number]');

    if (skuInput && categorySelect && subcategorySelect) {
        const updateSku = () => {
            const categoryInitial = categorySelect.selectedOptions[0]?.dataset.skuInitial;
            const subcategoryInitial = subcategorySelect.selectedOptions[0]?.dataset.skuInitial ?? '';
            skuInput.value = categoryInitial
                ? `${categoryInitial}${subcategoryInitial}-${skuInput.dataset.skuNumber}`
                : '';
        };

        categorySelect.addEventListener('change', updateSku);
        subcategorySelect.addEventListener('change', updateSku);
        updateSku();
    }

    const regularPriceInput = document.querySelector('[data-regular-price]');
    const discountPercentageInput = document.querySelector('[data-discount-percentage]');
    const salePriceInput = document.querySelector('[data-sale-price]');

    if (regularPriceInput && discountPercentageInput && salePriceInput) {
        const updateSalePrice = () => {
            const price = Number.parseFloat(regularPriceInput.value);
            const discountPercentage = Number.parseFloat(discountPercentageInput.value);

            if (! Number.isFinite(price) || ! Number.isFinite(discountPercentage) || discountPercentage <= 0 || discountPercentage > 100) {
                if (discountPercentage === 0) salePriceInput.value = '';
                return;
            }

            salePriceInput.value = (price * (1 - (discountPercentage / 100))).toFixed(2);
        };

        const updateDiscountPercentage = () => {
            const price = Number.parseFloat(regularPriceInput.value);
            const salePrice = Number.parseFloat(salePriceInput.value);

            if (! Number.isFinite(price) || price <= 0 || ! Number.isFinite(salePrice) || salePrice < 0 || salePrice >= price) {
                discountPercentageInput.value = '';
                return;
            }

            discountPercentageInput.value = (((price - salePrice) / price) * 100).toFixed(2).replace(/\.00$/, '');
        };

        regularPriceInput.addEventListener('input', () => {
            if (discountPercentageInput.value !== '') {
                updateSalePrice();
            } else if (salePriceInput.value !== '') {
                updateDiscountPercentage();
            }
        });
        discountPercentageInput.addEventListener('input', updateSalePrice);
        salePriceInput.addEventListener('input', updateDiscountPercentage);

        if (discountPercentageInput.value !== '') {
            updateSalePrice();
        } else if (salePriceInput.value !== '') {
            updateDiscountPercentage();
        }
    }

    document.querySelectorAll('[data-slug-target]').forEach((nameInput) => {
        const slugInput = document.querySelector(nameInput.dataset.slugTarget);
        if (! slugInput) return;

        let lastGeneratedSlug = slugify(nameInput.value);

        nameInput.addEventListener('input', () => {
            if (slugInput.value === '' || slugInput.value === lastGeneratedSlug) {
                slugInput.value = slugify(nameInput.value);
            }

            lastGeneratedSlug = slugify(nameInput.value);
        });
    });

    document.querySelectorAll('[data-image-source]').forEach((imageInput) => {
        const preview = document.querySelector('[data-image-preview]');
        if (! preview) return;

        const image = preview.querySelector('img');
        const placeholder = preview.querySelector('span');

        const updatePreview = () => {
            const file = imageInput.files?.[0];
            image.hidden = ! file;
            placeholder.hidden = Boolean(file);
            if (file) image.src = URL.createObjectURL(file);
        };

        image.addEventListener('error', () => {
            image.hidden = true;
            placeholder.hidden = false;
        });
        imageInput.addEventListener('change', updatePreview);
    });

    const initializeAdditionalImage = (imageInput) => {
        const field = imageInput.closest('.additional-image-field');
        const preview = field?.querySelector('[data-additional-image-preview]');
        const image = preview?.querySelector('img');
        const placeholder = preview?.querySelector('span');
        const button = field?.querySelector('[data-additional-image-button]');
        const fileName = field?.querySelector('[data-additional-image-name]');
        if (! image || ! placeholder || ! button) return;

        const originalSource = image.getAttribute('src');
        let objectUrl;

        button.addEventListener('click', () => imageInput.click());

        const updatePreview = () => {
            const file = imageInput.files?.[0];
            if (objectUrl) URL.revokeObjectURL(objectUrl);
            objectUrl = file ? URL.createObjectURL(file) : null;
            const source = objectUrl || originalSource;
            image.hidden = ! source;
            placeholder.hidden = Boolean(source);
            if (source) image.src = source;
            else image.removeAttribute('src');
            button.querySelector('span').textContent = source ? 'Change image' : 'Choose image';
            if (fileName) fileName.textContent = file?.name ?? '';
        };

        image.addEventListener('error', () => {
            image.hidden = true;
            placeholder.hidden = false;
        });
        imageInput.addEventListener('change', updatePreview);
    };

    document.querySelectorAll('[data-additional-image-source]').forEach(initializeAdditionalImage);

    const additionalImageGrid = document.querySelector('[data-additional-image-grid]');
    document.querySelector('[data-add-image-field]')?.addEventListener('click', () => {
        const field = additionalImageGrid?.querySelector('.additional-image-field')?.cloneNode(true);
        if (! field) return;

        const index = additionalImageGrid.children.length;
        const input = field.querySelector('[data-additional-image-source]');
        const id = `additionalImage${index}`;
        input.id = id;
        input.name = `additional_images[${index}]`;
        input.value = '';
        input.classList.remove('is-invalid');
        field.querySelectorAll('.invalid-feedback').forEach((error) => error.remove());
        field.querySelectorAll('label').forEach((label) => label.htmlFor = id);
        field.querySelector('.form-label').textContent = `Image ${index + 1}`;
        const image = field.querySelector('img');
        image.removeAttribute('src');
        image.hidden = true;
        image.alt = `Additional image ${index + 1} preview`;
        const placeholder = field.querySelector('[data-additional-image-preview] span');
        placeholder.hidden = false;
        placeholder.innerHTML = `<i class="bi bi-cloud-arrow-up"></i> Upload image ${index + 1}`;
        const button = field.querySelector('[data-additional-image-button]');
        button.setAttribute('aria-controls', id);
        button.setAttribute('aria-label', `Choose additional image ${index + 1}`);
        button.querySelector('span').textContent = 'Choose image';
        field.querySelector('[data-additional-image-name]').textContent = '';
        additionalImageGrid.append(field);
        initializeAdditionalImage(input);
        button.focus();
    });

    const imageUploadForm = document.querySelector('[data-image-upload-form]');

    if (imageUploadForm) {
        const preparedImages = new WeakMap();
        let submitting = false;

        const resizeImage = async (file) => {
            if (file.size <= 40 * 1024) return file;

            const bitmap = await createImageBitmap(file);
            try {
                const scale = Math.min(1, 1600 / Math.max(bitmap.width, bitmap.height));
                const canvas = document.createElement('canvas');
                canvas.width = Math.max(1, Math.round(bitmap.width * scale));
                canvas.height = Math.max(1, Math.round(bitmap.height * scale));
                canvas.getContext('2d').drawImage(bitmap, 0, 0, canvas.width, canvas.height);
                const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/webp', 0.8));
                if (!blob || blob.size >= file.size) return file;

                return new File([blob], `${file.name.replace(/\.[^.]+$/, '')}.webp`, {type: 'image/webp'});
            } finally {
                bitmap.close();
            }
        };

        const prepareImage = (file) => {
            if (!preparedImages.has(file)) {
                preparedImages.set(file, new Promise((resolve) => {
                    const timeout = window.setTimeout(() => resolve(file), 5000);
                    resizeImage(file).then(resolve, () => resolve(file)).finally(() => window.clearTimeout(timeout));
                }));
            }
            return preparedImages.get(file);
        };

        imageUploadForm.addEventListener('change', async (event) => {
            const fileInput = event.target;
            if (!fileInput.matches('input[type="file"][accept^="image/"]')) return;
            const file = fileInput.files?.[0];
            if (!file) return;
            const optimizedFile = await prepareImage(file);
            if (submitting || fileInput.files?.[0] !== file) return;
            try {
                const transfer = new DataTransfer();
                transfer.items.add(optimizedFile);
                fileInput.files = transfer.files;
            } catch {
                // Original files remain uploadable when the browser cannot replace a selected file.
            }
        });

        const submitButton = imageUploadForm.querySelector('button[type="submit"]');
        const originalButtonContent = submitButton?.innerHTML;
        const uploadError = imageUploadForm.querySelector('[data-product-form-error]');

        imageUploadForm.addEventListener('invalid', (event) => {
            if (uploadError) {
                uploadError.hidden = false;
                const fieldLabel = event.target.labels?.[0]?.textContent.trim() || 'Field';
                uploadError.textContent = `${fieldLabel}: ${event.target.validationMessage}`;
            }
        }, true);

        imageUploadForm.addEventListener('submit', (event) => {
            if (event.defaultPrevented) return;
            if (submitting) {
                event.preventDefault();
                return;
            }
            submitting = true;
            if (uploadError) uploadError.hidden = true;
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving product...';
            }
        });

        window.addEventListener('pageshow', () => {
            submitting = false;
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonContent;
            }
        });
    }

    document.querySelectorAll('[data-avatar-source]').forEach((avatarInput) => {
        avatarInput.addEventListener('change', () => {
            const file = avatarInput.files?.[0];
            const preview = document.querySelector('[data-avatar-preview]');
            if (! file || ! preview) return;

            let image = preview.querySelector('img');
            if (! image) {
                image = document.createElement('img');
                image.alt = 'Profile photo preview';
                preview.append(image);
            }

            preview.querySelector('span')?.remove();
            image.hidden = false;
            image.src = URL.createObjectURL(file);
        });
    });
});

document.querySelectorAll('[data-site-editor]').forEach((editor) => {
    const form = editor.querySelector('[data-site-content-form]');
    const status = editor.querySelector('[data-site-save-status]');
    let dirty = false;
    form?.addEventListener('input', () => {
        dirty = true;
        status.textContent = 'পরিবর্তন করা হয়েছে · এখনো সেভ হয়নি';
        status.classList.add('is-dirty');
    });
    form?.addEventListener('submit', () => { dirty = false; });
    window.addEventListener('beforeunload', (event) => {
        if (dirty) {
            event.preventDefault();
            event.returnValue = '';
        }
    });
    editor.querySelector('[data-site-page-select]')?.addEventListener('change', (event) => event.target.form.requestSubmit());
    editor.querySelectorAll('[data-site-image]').forEach((card) => {
        const image = card.querySelector('[data-site-image-preview]');
        const upload = card.querySelector('[data-site-image-upload]');
        const url = card.querySelector('[data-site-image-url]');
        let objectUrl;
        upload.addEventListener('change', () => {
            if (objectUrl) URL.revokeObjectURL(objectUrl);
            const file = upload.files[0];
            if (file) {
                objectUrl = URL.createObjectURL(file);
                image.hidden = false;
                image.src = objectUrl;
                card.querySelector('[data-site-image-name]').textContent = file.name;
            } else {
                image.hidden = !url.value;
                image.src = url.value;
            }
        });
        url.addEventListener('change', () => {
            if (!upload.files.length) image.hidden = !url.value;
            if (!upload.files.length && /^(https?:\/\/|\/(?!\/))/.test(url.value)) image.src = url.value;
        });
    });
});
