<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Pelanggan - SIREKA ASSI</title>
    <?php echo app('Illuminate\Foundation\Vite')('resources/js/app.js'); ?>
</head>
<body class="d-flex flex-column">
    <main class="page page-center">
        <div class="container-tight py-4">
            <div class="text-center mb-4"><img src="<?php echo e(asset('assets/img/assi_logo_with_name.png')); ?>" width="180" alt="SIREKA ASSI"></div>
            <div class="card">
                <div class="card-body">
                    <h1 class="h2 text-center mb-3">Portal Pelanggan</h1>
                    <p class="text-secondary">Pantau proyek, berikan persetujuan, dan sampaikan masukan melalui akun pribadi Anda.</p>
                    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <form class="customer_form" method="POST" action="<?php echo e(route('customer.login.process')); ?>">
                        <?php echo csrf_field(); ?>
                        <div class="mb-3"><label for="email" class="form-label">Email</label><input id="email" name="email" type="email" class="form-control" required maxlength="64" value="<?php echo e(old('email')); ?>" autocomplete="username"></div>
                        <div class="mb-3"><label for="password" class="form-label">Kata Sandi</label><input id="password" name="password" type="password" class="form-control" required maxlength="1024" autocomplete="current-password"></div>
                        <button class="btn btn-primary w-100" type="submit">Masuk</button>
                    </form>
                </div>
                <div class="card-footer text-center"><a href="<?php echo e(route('login')); ?>">Login Karyawan</a> &middot; <a href="<?php echo e(route('admin.login')); ?>">Login Admin</a></div>
            </div>
        </div>
    </main>
    <script src="<?php echo e(asset('assets/dist/js/jquery-3.7.1.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/dist/js/jquery-validation/jquery.validate.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/dist/js/jquery-validation/additional-methods.min.js')); ?>"></script>
    <?php echo $__env->make('customer.validation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /home/snowy/projects/assi-repair/resources/views/customer/login.blade.php ENDPATH**/ ?>