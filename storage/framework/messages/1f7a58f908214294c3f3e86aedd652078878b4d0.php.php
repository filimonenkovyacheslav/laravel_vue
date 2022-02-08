<?php $__env->startSection('content'); ?>
    <div class="container site-messages">
        <div class="row">
            <div class="col-md-12 col-xs-12">
                <?php if(session('status')): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo e(session('status')); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php switch($name):
        case ('home'): ?>
            <index-component :params="<?php echo e($params); ?>"></index-component>
            <?php break; ?>
        <?php case ('admin'): ?>
            <admin-component :params="<?php echo e($params); ?>"></admin-component>
            <?php break; ?>


        <?php case ('property.list.frontend'): ?><?php case ('agency_list_frontend'): ?>
            <properties-list-frontend :params="<?php echo e($params); ?>"></properties-list-frontend>
            <?php break; ?>
        <?php case ('property.view.frontend'): ?><?php case ('agency_view_frontend'): ?>
            <property-view-frontend :params="<?php echo e($params); ?>"></property-view-frontend>
            <?php break; ?>

        <?php case ('property.list.admin'): ?><?php case ('agency_list_admin'): ?>
            <properties-list-admin :params="<?php echo e($params); ?>"></properties-list-admin>
            <?php break; ?>
        <?php case ('property.view.admin'): ?><?php case ('agency_view_admin'): ?>
            <property-view-admin :params="<?php echo e($params); ?>"></property-view-admin>
            <?php break; ?>
        <?php case ('property.edit.admin'): ?><?php case ('agency_edit_admin'): ?>
            <property-edit-admin :params="<?php echo e($params); ?>"></property-edit-admin>
            <?php break; ?>


        <?php default: ?>
            <div class="container no-content">
                <div class="row">
                    <div class="col-md-12 col-xs-12">
                        <span><?php echo e(__('Oops! This page is missing')); ?>...</span>
                    </div>
                </div>
            </div>
            <?php break; ?>
    <?php endswitch; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>