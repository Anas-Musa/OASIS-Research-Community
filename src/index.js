import { Toast } from 'bootstrap';

import $ from "jquery";
import { instance, webURL } from "./api/config.api.js";

import "block-ui";

(function () {
    if (!!$('#landing_page_contact_form').length) {
        $('#landing_page_contact_form').on('submit', (event) => {
            event.preventDefault();
            let form = event.currentTarget;
            let submit_btn = $(form.landing_page_contact_form_button);

            const alertElement = $(form).find('.alert');
            var scrollTop = $("#contact-us").offset().top;

            const toastElement = $('#kt_docs_toast_toggle');
            const toast = Toast.getOrCreateInstance(toastElement, {
                // delay: 5000
            });

            function stopLoading() {
                submit_btn.removeClass("disabled");
                submit_btn.attr('data-kt-indicator', 'off');
            }

            function startLoading() {
                submit_btn.addClass("disabled");
                submit_btn.attr('data-kt-indicator', 'on');
            }

            let name = $(form.name).val();
            let email = $(form.email).val();
            let subject = $(form.subject).val();
            let message = $(form.message).val();

            let payload = {
                name,
                email,
                subject,
                message
            };

            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            startLoading();
            instance.post('quotes', payload)
                .then(response => {
                    stopLoading();
                    if (response.data.success) {
                        $(window).animate().scrollTop(scrollTop);
                        form.reset();
                        toastElement.find('.toast-body').html('Thank you for sending us a message.')
                        toast.show();
                    }
                })
                .catch(error => {
                    stopLoading();
                    $(window).animate().scrollTop(scrollTop);
                    alertElement.toggleClass('d-none');

                    setTimeout(() => {
                        alertElement.toggleClass('d-none');
                    }, 5000);

                    if (error.response.status === 404) {
                        $(alertElement).find('.alert-content').html(`${error.message}`);
                    } else {
                        $(alertElement).find('.alert-content').html(`${error.response.data.messages.join("<br>")}`);
                    }
                })
        });
    }

    if (!!$('#members_sign_up_form').length) {
        $('#members_sign_up_form').on('submit', (event) => {
            event.preventDefault();

            const toastElement = $('#kt_docs_toast_toggle');
            const toast = Toast.getOrCreateInstance(toastElement, {
                // delay: 5000
            });

            let form = event.currentTarget;
            let submit_btn = $(form.members_sign_up_submit);

            const alertElement = $(form).find('.alert');
            var scrollTop = $(form).offset().top;

            function stopLoading() {
                submit_btn.removeClass("disabled");
                submit_btn.attr('data-kt-indicator', 'off');
            }

            function startLoading() {
                submit_btn.addClass("disabled");
                submit_btn.attr('data-kt-indicator', 'on');
            }

            function showAlertBox() {
                alertElement.toggleClass('d-none');

                setTimeout(() => {
                    alertElement.toggleClass('d-none');
                }, 5000);
            }


            let email = $(form.email).val();
            let password = $(form.password).val();
            let confirm_password = $(form.confirm_password).val();
            let terms_and_conditions = $(form.terms_and_conditions).is(':checked');

            let passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@.#\$%\^&\*])(?=.{8,})/;
            // var strongRegex = new RegExp("^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@.#\$%\^&\*])(?=.{8,})");

            if (!terms_and_conditions) {
                $(window).animate().scrollTop(scrollTop);
                $(alertElement).find('.alert-content').html(`Accept the terms`);
                showAlertBox();
                return;
            }

            if (!passwordRegex.test(password)) {
                $(window).animate().scrollTop(scrollTop);
                $(alertElement).find('.alert-content').html(`Use 8 or more characters with a mix of upper and lower letters, numbers & symbols.`);
                showAlertBox();
                return;
            }

            if (password !== confirm_password) {
                $(window).animate().scrollTop(scrollTop);
                $(alertElement).find('.alert-content').html(`Passwords do not match`);
                showAlertBox();
                return;
            }

            let payload = {
                email,
                password
            }

            startLoading();
            instance.post('signup', payload)
                .then(response => {
                    stopLoading();
                    if (response.data.success) {
                        form.reset();
                        $(window).animate().scrollTop(scrollTop);
                        toastElement.find('.toast-body').html(response.data.messages.join(''));
                        toast.show();
                        sessionStorage.setItem('signup', JSON.stringify({ email, message: response.data.messages.join('') }));
                        window.location.href = `${webURL}verify.html`;
                    }
                })
                .catch(error => {
                    stopLoading();
                    $(window).animate().scrollTop(scrollTop);
                    if (error.response.status === 404) {
                        $(alertElement).find('.alert-content').html(`${error.message}`);
                    } else {
                        $(alertElement).find('.alert-content').html(`${error.response.data.messages.join("<br>")}`);
                    }

                    showAlertBox();

                })
        });
    }

    if (window.location.pathname.includes('verify.html')) {
        let sessionData = sessionStorage.getItem('signup');

        if (!sessionData) {
            window.location.href = `${webURL}`;
            return;
        }

        sessionData = JSON.parse(sessionData);
        $('#alert-message').html(sessionData.message);

        if (!!$('#resend_otp').length) {
            $('#resend_otp').on('click', () => {
                const toastElement = $('#kt_docs_toast_toggle');
                const toast = Toast.getOrCreateInstance(toastElement, {
                    // delay: 5000
                });

                $.blockUI({
                    message: `<span class="indicator-progress text-white d-block">
                                sending... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>`,
                });

                instance.post('resend_otp', { email: sessionData.email })
                    .then(response => {
                        $.unblockUI();
                        if (response.data.success) {
                            toastElement.find('.toast-body').html(`OTP sent.`)
                            toast.show();
                        }
                    })
                    .catch(error => {
                        $.unblockUI();
                        if (error.response.status === 404) {
                            toastElement.find('.toast-body').html(`${error.message}`);
                        } else {
                            toastElement.find('.toast-body').html(`${error.response.data.messages.join("<br>")}`);
                        }
                        toast.show();
                    });
            });
        }

        if (!!$('#kt_sing_in_two_steps_form')) {
            $('#kt_sing_in_two_steps_form').on('submit', (event) => {
                event.preventDefault();
                let form = event.currentTarget;
                let submit_btn = $(form.kt_sing_in_two_steps_submit);

                const alertElement = $(form).find('.alert');
                var scrollTop = $(form).offset().top;

                const toastElement = $('#kt_docs_toast_toggle');
                const toast = Toast.getOrCreateInstance(toastElement, {
                    // delay: 5000
                });

                function stopLoading() {
                    submit_btn.removeClass("disabled");
                    submit_btn.attr('data-kt-indicator', 'off');
                }

                function startLoading() {
                    submit_btn.addClass("disabled");
                    submit_btn.attr('data-kt-indicator', 'on');
                }

                function showAlertBox() {
                    alertElement.toggleClass('d-none');

                    setTimeout(() => {
                        alertElement.toggleClass('d-none');
                    }, 5000);
                }

                let code_1 = $(form.code_1).val();
                let code_2 = $(form.code_2).val();
                let code_3 = $(form.code_3).val();
                let code_4 = $(form.code_4).val();
                let code_5 = $(form.code_5).val();
                let code_6 = $(form.code_6).val();

                if (code_1 === "" || code_2 === "" || code_3 === "" || code_4 === "" || code_5 === "" || code_6 === "") {
                    $(window).animate().scrollTop(scrollTop);
                    $(alertElement).find('.alert-content').html(`Fill in your OTP correctly`);
                    showAlertBox();
                    return;
                }

                startLoading();
                instance.post('verify', {
                    email: sessionData.email,
                    otp: `${code_1}${code_2}${code_3}${code_4}${code_5}${code_6}`
                })
                    .then(response => {
                        stopLoading();

                        if (response.data.success) {
                            form.reset();
                            $(window).animate().scrollTop(scrollTop);
                            toastElement.find('.toast-body').html('Account verified.');
                            sessionStorage.removeItem('signup');
                            toast.show();
                            toastElement[0].addEventListener('hidden.bs.toast', () => {
                                window.location.href = `${webURL}`;
                            });
                        }
                    })
                    .catch(error => {
                        console.log(error.response);
                        stopLoading();
                        if (error.response.status === 404) {
                            $(alertElement).find('.alert-content').html(`${error.message}`);
                        } else {
                            $(alertElement).find('.alert-content').html(`${error.response.data.messages.join("<br>")}`);
                        }

                        showAlertBox();
                    });
            });
        }
    }

}());




