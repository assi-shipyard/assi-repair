<?php $__env->startPush('scripts'); ?>
<script>
    $(function () {
        $.extend($.validator.messages, {
            required: 'Kolom ini wajib diisi.',
            email: 'Format email tidak valid.',
            number: 'Masukkan angka yang valid.',
            digits: 'Masukkan angka saja.',
            maxlength: $.validator.format('Maksimal {0} karakter.'),
            minlength: $.validator.format('Minimal {0} karakter.'),
            equalTo: 'Konfirmasi tidak sesuai.',
            min: $.validator.format('Nilai minimal {0}.'),
            max: $.validator.format('Nilai maksimal {0}.')
        });
        $('form.customer_form').each(function () {
            $(this).validate({
                errorClass: 'text-danger small',
                errorElement: 'div',
                rules: { password_confirmation: { equalTo: '[name="password"]' } }
            });
        });
    });
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH /home/snowy/projects/assi-repair/resources/views/customer/validation.blade.php ENDPATH**/ ?>