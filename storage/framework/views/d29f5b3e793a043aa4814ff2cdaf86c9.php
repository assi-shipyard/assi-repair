<script>
    $(function () {
        const cancel_modal = $('#cancel-request-modal');
        const cancel_form = $('#cancel-request-form');
        const cancel_validator = cancel_form.validate({
            errorClass: 'is-invalid',
            validClass: 'is-valid',
            errorElement: 'div',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                element.after(error);
            },
            rules: { cancellation_reason: { required: true, minlength: 5, maxlength: 2000 } },
            messages: {
                cancellation_reason: {
                    required: 'Alasan pembatalan wajib diisi.',
                    minlength: 'Alasan pembatalan minimal 5 karakter.',
                    maxlength: 'Alasan pembatalan maksimal 2000 karakter.',
                },
            },
            submitHandler: function (form) {
                $(form).find('button[type="submit"]').prop('disabled', true);
                form.submit();
            },
        });

        cancel_modal.on('show.bs.modal', function (event) {
            const trigger = $(event.relatedTarget);
            if (!trigger.length) {
                return;
            }

            cancel_form.attr('action', trigger.data('action'));
            cancel_form.find('[name="cancel_request_id"]').val(trigger.data('request-id'));
            ['ship', 'project', 'schedule', 'dock', 'status'].forEach(function (field) {
                cancel_modal.find('[data-field="' + field + '"]').text(trigger.data(field));
            });
            cancel_modal.find('[data-field="approved-note"]').toggleClass('d-none', String(trigger.data('approved')) !== '1');
            cancel_form.find('[name="cancellation_reason"]').val('').removeClass('is-invalid is-valid');
            cancel_validator.resetForm();
        });

        cancel_modal.on('shown.bs.modal', function () {
            cancel_form.find('[name="cancellation_reason"]').trigger('focus');
        });

        const failed_request_id = <?php echo json_encode($errors->has('cancellation_reason') ? (string) old('cancel_request_id', '') : '', 512) ?>;
        const failed_trigger = $('.js-cancel-request').filter(function () {
            return failed_request_id !== '' && $(this).data('request-id') === failed_request_id;
        });
        if (failed_trigger.length) {
            const failed_reason = cancel_form.find('[name="cancellation_reason"]').val();
            cancel_modal.one('shown.bs.modal', function () {
                cancel_form.find('[name="cancellation_reason"]').val(failed_reason).addClass('is-invalid');
            });
            bootstrap.Modal.getOrCreateInstance(cancel_modal[0]).show(failed_trigger[0]);
        }
    });
</script>
<?php /**PATH /home/snowy/projects/assi-repair/resources/views/docking-space-request/partials/cancel-modal-script.blade.php ENDPATH**/ ?>