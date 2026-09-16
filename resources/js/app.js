const trackingEndpoint = document.querySelector('meta[name="tracking-endpoint"]')?.content;
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

const track = (event, metadata = {}) => {
    if (! trackingEndpoint || ! csrfToken) return;

    fetch(trackingEndpoint, {
        method: 'POST',
        keepalive: true,
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json'},
        body: JSON.stringify({event, path: window.location.pathname, metadata}),
    }).catch(() => {});
};

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-category-slider]').forEach((slider) => {
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        let interacting = false;
        let resumeAfter = 0;
        slider.addEventListener('pointerenter', () => interacting = true);
        slider.addEventListener('pointerleave', () => interacting = false);
        slider.addEventListener('pointerdown', () => resumeAfter = Date.now() + 5000);
        slider.addEventListener('wheel', () => resumeAfter = Date.now() + 5000, {passive: true});

        window.setInterval(() => {
            if (document.hidden || reducedMotion.matches || interacting || slider.contains(document.activeElement) || Date.now() < resumeAfter) return;
            const maxScroll = slider.scrollWidth - slider.clientWidth;
            const firstCard = slider.firstElementChild;
            if (maxScroll <= 1 || !firstCard) return;
            const step = firstCard.getBoundingClientRect().width + parseFloat(getComputedStyle(slider).columnGap);
            const nextScroll = slider.scrollLeft >= maxScroll - 2 ? 0 : Math.min(maxScroll, slider.scrollLeft + step);
            slider.scrollTo({left: nextScroll, behavior: 'smooth'});
        }, 3000);
    });

    const openLinkedTicket = () => {
        const ticket = document.getElementById(window.location.hash.slice(1));
        if (ticket?.classList.contains('customer-ticket')) ticket.open = true;
    };
    openLinkedTicket();
    window.addEventListener('hashchange', openLinkedTicket);

    document.querySelectorAll('[data-account-copy]').forEach((button) => {
        button.addEventListener('click', async () => {
            const status = document.querySelector('[data-copy-status]');
            try {
                if (navigator.clipboard?.writeText) {
                    await navigator.clipboard.writeText(button.dataset.accountCopy);
                } else {
                    const input = document.createElement('textarea');
                    input.value = button.dataset.accountCopy;
                    input.style.position = 'fixed';
                    input.style.opacity = '0';
                    document.body.append(input);
                    input.select();
                    const copied = document.execCommand('copy');
                    input.remove();
                    if (!copied) throw new Error('Copy failed');
                }
                if (status) status.textContent = 'কপি হয়েছে।';
            } catch {
                if (status) status.textContent = 'কপি করা যায়নি। লেখাটি নির্বাচন করে কপি করুন।';
            }
        });
    });

    const notification = document.querySelector('[data-store-notification]');

    if (notification) {
        let dismissTimer;
        const scheduleDismiss = () => {
            window.clearTimeout(dismissTimer);
            dismissTimer = window.setTimeout(() => notification.remove(), 5000);
        };

        notification.querySelector('[data-dismiss-notification]')?.addEventListener('click', () => {
            window.clearTimeout(dismissTimer);
            notification.remove();
        });
        notification.addEventListener('mouseenter', () => window.clearTimeout(dismissTimer));
        notification.addEventListener('mouseleave', scheduleDismiss);
        notification.addEventListener('focusin', () => window.clearTimeout(dismissTimer));
        notification.addEventListener('focusout', scheduleDismiss);
        scheduleDismiss();
    }

    const productGalleryMain = document.querySelector('[data-product-gallery-main]');
    const productGalleryButtons = document.querySelectorAll('[data-product-gallery-image]');

    productGalleryButtons.forEach((button) => {
        button.addEventListener('click', () => {
            if (! productGalleryMain) return;

            productGalleryMain.src = button.dataset.productGalleryImage;
            productGalleryButtons.forEach((galleryButton) => galleryButton.classList.remove('active'));
            button.classList.add('active');
        });
    });

    document.querySelectorAll('.product-quantity-stepper').forEach((stepper) => {
        const input = stepper.querySelector('[data-quantity-input]');
        const decreaseButton = stepper.querySelector('[data-quantity-decrease]');
        const increaseButton = stepper.querySelector('[data-quantity-increase]');
        if (! input) return;

        const updateButtons = () => {
            const value = Number.parseInt(input.value, 10) || 1;
            const minimum = Number.parseInt(input.min, 10) || 1;
            const maximum = Number.parseInt(input.max, 10);
            input.value = Math.max(minimum, Number.isNaN(maximum) ? value : Math.min(value, maximum));
            decreaseButton.disabled = Number.parseInt(input.value, 10) <= minimum;
            increaseButton.disabled = ! Number.isNaN(maximum) && Number.parseInt(input.value, 10) >= maximum;
        };

        decreaseButton.addEventListener('click', () => { input.stepDown(); updateButtons(); });
        increaseButton.addEventListener('click', () => { input.stepUp(); updateButtons(); });
        input.addEventListener('input', updateButtons);
        updateButtons();
    });

    if (window.location.pathname === '/login' || window.location.pathname === '/dashboard') return;

    track('page_view', {title: document.title});
    if (window.location.pathname === '/') track('landing_page_view');
    if (window.location.pathname.startsWith('/chair/')) track('product_view', {product: document.querySelector('h1')?.textContent?.trim() || ''});
    if (window.location.pathname === '/checkout') track('checkout_open');

    document.addEventListener('click', (event) => {
        const target = event.target.closest('a, button');
        if (! target) return;
        const href = target.getAttribute('href') || '';
        track(href.startsWith('tel:') || href.startsWith('sms:') || href.includes('wa.me') || href.includes('whatsapp') ? 'phone_whatsapp_click' : 'button_click', {
            label: target.getAttribute('aria-label') || target.textContent?.trim().slice(0, 100) || '',
            href,
        });
    });

    document.addEventListener('submit', (event) => {
        const form = event.target;
        const configuredEvent = form.dataset.trackEvent;

        if (configuredEvent) {
            track(configuredEvent);
        }
    });

    const paymentChoices = document.querySelectorAll('[data-payment-choice]');
    const paymentDetails = document.querySelector('[data-payment-details]');
    const paymentNumber = document.querySelector('[data-selected-payment-number]');
    const paymentLogo = document.querySelector('[data-selected-payment-logo]');
    const copyPaymentButton = document.querySelector('[data-copy-payment-number]');
    const paymentPhone = document.querySelector('#paymentPhone');
    const transactionId = document.querySelector('#transactionId');

    const updatePaymentDetails = (choice) => {
        if (! paymentDetails || ! choice) return;

        const isCashOnDelivery = choice.value === 'cod';
        const number = choice.dataset.paymentNumber || '';
        paymentDetails.hidden = isCashOnDelivery;
        paymentPhone?.toggleAttribute('required', ! isCashOnDelivery);
        transactionId?.toggleAttribute('required', ! isCashOnDelivery);

        if (! isCashOnDelivery) {
            paymentNumber.textContent = number || 'নম্বর সেট করা হয়নি';
            paymentLogo.src = choice.dataset.paymentLogo;
            paymentLogo.alt = choice.dataset.paymentName;
            copyPaymentButton.dataset.copyValue = number;
            copyPaymentButton.disabled = number === '';
        }
    };

    paymentChoices.forEach((choice) => {
        choice.addEventListener('change', () => updatePaymentDetails(choice));
    });
    updatePaymentDetails(document.querySelector('[data-payment-choice]:checked'));

    copyPaymentButton?.addEventListener('click', async () => {
        const number = copyPaymentButton.dataset.copyValue;
        if (! number) return;

        if (navigator.clipboard?.writeText) {
            await navigator.clipboard.writeText(number);
        } else {
            const temporaryInput = document.createElement('textarea');
            temporaryInput.value = number;
            document.body.append(temporaryInput);
            temporaryInput.select();
            document.execCommand('copy');
            temporaryInput.remove();
        }
        const originalText = copyPaymentButton.innerHTML;
        copyPaymentButton.innerHTML = '<i class="bi bi-check2"></i> কপি হয়েছে';
        window.setTimeout(() => { copyPaymentButton.innerHTML = originalText; }, 1600);
    });

    let scrollTracked = false;
    const trackScrollDepth = () => {
        const progress = (window.scrollY + window.innerHeight) / document.documentElement.scrollHeight;
        if (! scrollTracked && progress >= 0.75) {
            scrollTracked = true;
            track('scroll_engagement');
        }
    };
    window.addEventListener('scroll', trackScrollDepth, {passive: true});
    trackScrollDepth();

});
