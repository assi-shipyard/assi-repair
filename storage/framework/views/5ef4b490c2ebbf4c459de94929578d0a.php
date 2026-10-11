<script>
    $.validator.addMethod('price_code', function (value, element) {
        return this.optional(element) || /^[A-Za-z0-9._-]+$/.test(value);
    }, 'Kode hanya boleh berisi huruf, angka, titik, garis bawah, dan strip.');
    $.validator.addMethod('price_decimal', function (value, element, places) {
        return this.optional(element) || new RegExp('^\\d+(\\.\\d{1,' + places + '})?$').test(value);
    }, 'Jumlah angka desimal tidak valid. Gunakan titik untuk desimal.');
    const price_form_options = {
        errorElement: 'div',
        errorClass: 'invalid-feedback',
        highlight: function (element) { $(element).addClass('is-invalid'); },
        unhighlight: function (element) { $(element).removeClass('is-invalid'); }
    };
    $.extend($.validator.messages, {
        required: 'Kolom ini wajib diisi.',
        maxlength: $.validator.format('Maksimal {0} karakter.'),
        number: 'Masukkan angka yang valid.',
        min: $.validator.format('Nilai minimal {0}.'),
        max: $.validator.format('Nilai maksimal {0}.'),
        step: 'Jumlah angka desimal tidak valid.'
    });
</script>
<?php /**PATH /home/snowy/projects/assi-repair/resources/views/price-book/partials/validation.blade.php ENDPATH**/ ?>