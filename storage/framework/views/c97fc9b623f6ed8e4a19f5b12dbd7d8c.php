<?php $__env->startPush('scripts'); ?>
<script>
    $(function () {
        $.validator.addMethod('profile_photo_size', function (value, element) {
            return this.optional(element) || !element.files || element.files[0].size <= 2097152;
        }, 'Ukuran foto profil maksimal 2 MB.');
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
                rules: {
                    password_confirmation: { equalTo: '[name="password"]' },
                    profile_photo: { profile_photo_size: true, extension: 'jpg|jpeg|png|webp' }
                },
                messages: { profile_photo: { extension: 'Foto profil harus berformat JPG, JPEG, PNG, atau WEBP.' } }
            });
        });
    });
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH /home/snowy/projects/assi-repair/resources/views/customer/validation.blade.php ENDPATH**/ ?>