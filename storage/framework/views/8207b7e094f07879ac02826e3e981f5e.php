<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="shortcut icon" href="<?php echo e(secure_asset('assets/img/favicon.ico')); ?>" type="image/x-icon">
    <title>Login Admin - Sistem Informasi Reparasi Kapal ASSI</title>

    <link href="<?php echo e(secure_asset('assets/dist/css/tabler.min.css?1692870487')); ?>" rel="stylesheet">
    <link href="<?php echo e(secure_asset('assets/dist/css/tabler-flags.min.css?1692870487')); ?>" rel="stylesheet">
    <link href="<?php echo e(secure_asset('assets/dist/css/tabler-payments.min.css?1692870487')); ?>" rel="stylesheet">
    <link href="<?php echo e(secure_asset('assets/dist/css/tabler-vendors.min.css?1692870487')); ?>" rel="stylesheet">
    <link href="<?php echo e(secure_asset('assets/dist/css/demo.min.css?1692870487')); ?>" rel="stylesheet">
    <style>
        @import url('https://rsms.me/inter/inter.css');
        :root {
            --tblr-font-sans-serif: 'Inter Var', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
        }
        body {
            font-feature-settings: "cv03", "cv04", "cv11";
        }
    </style>
</head>
<body class="d-flex flex-column">
    <div class="page page-center">
        <div class="container container-tight py-4">
            <div class="text-center mb-4">
                <a class="navbar-brand navbar-brand-autodark">
                    <img src="<?php echo e(secure_asset('assets/img/assi_logo_with_name.png')); ?>" class="w-75 mx-auto" alt="ASSI">
                </a>
            </div>

            <div class="card card-md border-warning">
                <form action="<?php echo e(route('admin.login.process')); ?>" method="POST" autocomplete="off" id="admin_login_form">
                    <?php echo csrf_field(); ?>
                    <div class="card-body">
                        <h2 class="h2 text-center fw-bold mb-1">Login Admin</h2>
                        <p class="text-secondary text-center mb-4">Halaman ini hanya untuk akun administrator.</p>

                        <?php if(session('error')): ?>
                            <div class="alert alert-danger" role="alert"><?php echo e(session('error')); ?></div>
                        <?php endif; ?>

                        <?php if($errors->any()): ?>
                            <div class="alert alert-danger" role="alert">
                                <ul class="mb-0">
                                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($error); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label" for="username">Username Admin</label>
                            <input type="text" name="username" id="username" class="form-control" value="<?php echo e(old('username')); ?>" placeholder="Masukkan username admin" required autocomplete="off">
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="password">Password</label>
                            <input type="password" name="password" id="password" class="form-control" placeholder="Password" required autocomplete="off">
                        </div>

                        <div class="mb-3">
                            <label class="form-check">
                                <input type="checkbox" name="remember" class="form-check-input" <?php echo e(old('remember') ? 'checked' : ''); ?>>
                                <span class="form-check-label">Ingat saya</span>
                            </label>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="<?php echo e(route('login')); ?>" class="btn btn-light w-100">Login Karyawan</a>
                            <button type="submit" class="btn btn-warning w-100">Masuk Admin</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="<?php echo e(secure_asset('assets/dist/js/tabler.min.js')); ?>" defer></script>
    <script src="<?php echo e(secure_asset('assets/dist/js/demo.min.js')); ?>" defer></script>
    <script src="<?php echo e(secure_asset('assets/dist/js/jquery-3.7.1.min.js')); ?>"></script>
    <script src="<?php echo e(secure_asset('assets/dist/js/jquery-validation/jquery.validate.min.js')); ?>"></script>

    <script>
        $(document).ready(function() {
            $('#admin_login_form').validate({
                rules: {
                    username: {
                        required: true,
                        minlength: 3,
                        maxlength: 64
                    },
                    password: {
                        required: true
                    }
                },
                messages: {
                    username: {
                        required: 'Username admin wajib diisi.',
                        minlength: 'Username admin minimal 3 karakter.',
                        maxlength: 'Username admin maksimal 64 karakter.'
                    },
                    password: {
                        required: 'Password wajib diisi.'
                    }
                },
                errorElement: 'span',
                errorPlacement: function(error, element) {
                    error.addClass('invalid-feedback');
                    element.closest('.mb-3').append(error);
                },
                highlight: function(element) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function(element) {
                    $(element).removeClass('is-invalid');
                }
            });
        });
    </script>
</body>
</html>
<?php /**PATH D:\wamp64\www\assi-repair\resources\views\login\admin.blade.php ENDPATH**/ ?>