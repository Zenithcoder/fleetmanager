<?php $__env->startSection('extra_css'); ?>
<style type="text/css">
.nav-tabs-custom>.nav-tabs>li.active{border-top-color:#3c8dbc !important;}
.custom_color.active
{
  color: #fff;
  background-color: #02bcd1 !important;
}
</style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection("breadcrumb"); ?>
<li class="breadcrumb-item"><a href="<?php echo e(url('admin/')); ?>"><?php echo app('translator')->getFromJson('fleet.home'); ?></a></li>
<li class="breadcrumb-item active"><?php echo app('translator')->getFromJson('fleet.myProfile'); ?></li>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
  <div class="col-md-3">
  <!-- Profile Image -->
    <div class="card card-info card-outline">
      <div class="card-body box-profile">
        <div class="text-center">
          <?php if($data->getMeta('driver_image') != null): ?>
            <?php if(starts_with($data->getMeta('driver_image'),'http')): ?>
              <?php ($src = $data->getMeta('driver_image')); ?>
            <?php else: ?>
              <?php ($src=asset('uploads/'.$data->getMeta('driver_image'))); ?>
            <?php endif; ?>
            <img src="<?php echo e($src); ?>" class="profile-user-img img-responsive img-circle"  alt="User profile picture">
          <?php else: ?>
            <img src="<?php echo e(asset("assets/images/no-user.jpg")); ?>" alt="User profile picture" class="profile-user-img img-responsive img-circle">
          <?php endif; ?>
        </div>
        <h3 class="profile-username text-center"> <?php echo e($data->getMeta('first_name')); ?> <?php echo e($data->getMeta('last_name')); ?></h3>
        <ul class="list-group list-group-unbordered">
          <li class="list-group-item">
            <b>
            <?php echo app('translator')->getFromJson('fleet.total'); ?>
            <?php echo app('translator')->getFromJson('fleet.bookings'); ?></b> <a class="pull-right"> <?php echo e($total); ?> </a>
          </li>
        </ul>
        <a href="<?php echo e(url('admin/change-details/'.Auth::user()->id)); ?>" class="btn btn-info btn-block"><b><?php echo app('translator')->getFromJson('fleet.editProfile'); ?></b></a>
      </div>
    </div>
    <!-- About Me Box -->
    <div class="card card-info">
      <div class="card-header">
      <h3 class="card-title"><?php echo app('translator')->getFromJson('fleet.about_me'); ?></h3>
      </div>
      <!-- /.box-header -->
      <div class="card-body">
        <strong><i class="fa fa-user margin-r-5"></i> <?php echo app('translator')->getFromJson('fleet.personal_info'); ?></strong>
        <p class="text-muted">
          <?php echo e($data->getMeta('first_name')); ?> <?php echo e($data->getMeta('middle_name')); ?> <?php echo e($data->getMeta('last_name')); ?>

          <br>
          <?php echo e($data->getMeta('phone')); ?>

          <br>
          <?php echo e($data->email); ?>

          <br>
          <?php echo e($data->getMeta('address')); ?>

        </p>
        <hr>
        <strong><i class="fa fa-file-pdf-o margin-r-5"></i> <?php echo app('translator')->getFromJson('fleet.doc_info'); ?></strong>

        <p class="text-muted">
          <?php echo app('translator')->getFromJson('fleet.licenseNumber'); ?>:<?php echo e($data->getMeta('license_number')); ?>

          <br>
          <?php echo app('translator')->getFromJson('fleet.issueDate'); ?>:<?php echo e($data->getMeta('issue_date')); ?>

          <br>
          <?php echo app('translator')->getFromJson('fleet.expirationDate'); ?>:<?php echo e($data->getMeta('exp_date')); ?>

          <br>
          <?php echo app('translator')->getFromJson('fleet.employee_id'); ?>:<?php echo e($data->getMeta('emp_id')); ?>

          <br>
          <?php echo app('translator')->getFromJson('fleet.contract'); ?>:<?php echo e($data->getMeta('contract_number')); ?>

        </p>
        <hr>
        <p>
          <?php if($data->getMeta('license_image') != null): ?>
          <a href="<?php echo e(asset('uploads/'.$data->getMeta('license_image'))); ?>" target="_blank" class="btn btn-info">
          <?php echo app('translator')->getFromJson('fleet.lic_photo'); ?>
          </a>
          <?php endif; ?>
        </p>
        <p>
          <?php if($data->getMeta('documents') != null): ?>
            <a href="<?php echo e(asset('uploads/'.$data->getMeta('documents'))); ?>" target="_blank" class="btn btn-info">
            <?php echo app('translator')->getFromJson('fleet.documents'); ?>
            </a>
          <?php endif; ?>
        </p>
      </div>
    </div>
    <!-- /.box -->
  </div>

  <div class="col-md-9">
    <div class="card">
      <div class="card-header p-2">
        <ul class="nav nav-pills">
          <li class="nav-item"><a class="nav-link custom_color active" href="#activity" data-toggle="tab"><?php echo app('translator')->getFromJson('fleet.activity'); ?></a></li>
          <li class="nav-item"><a class="nav-link custom_color" href="#upcoming" data-toggle="tab"><?php echo app('translator')->getFromJson('fleet.upcoming'); ?></a></li>
        </ul>
      </div>
      <div class="card-body">
        <div class="tab-content">
          <div class="active tab-pane" id="activity">
            <h4><?php echo app('translator')->getFromJson('menu.my_bookings'); ?></h4>
            <div class="table-responsive">
              <table class="table driver_table">
                <thead class="thead-inverse">
                  <tr>
                    <th><?php echo app('translator')->getFromJson('fleet.customer'); ?></th>
                    <th><?php echo app('translator')->getFromJson('fleet.vehicle'); ?></th>
                    <th><?php echo app('translator')->getFromJson('fleet.pickup'); ?></th>
                    <th><?php echo app('translator')->getFromJson('fleet.dropoff'); ?></th>
                    <th><?php echo app('translator')->getFromJson('fleet.pickup_addr'); ?></th>
                    <th><?php echo app('translator')->getFromJson('fleet.dropoff_addr'); ?></th>
                    <th><?php echo app('translator')->getFromJson('fleet.passengers'); ?></th>
                  </tr>
                </thead>
                <tbody>
                  <?php $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <?php if($row->getMeta('ride_status') == "Completed" || $row->status == 1): ?>
                  <tr>
                    <td><?php echo e($row->customer->name); ?></td>
                    <td><?php echo e($row->vehicle['make']); ?> - <?php echo e($row->vehicle['model']); ?> - <?php echo e($row->vehicle['license_plate']); ?></td>
                    <td>
                    <?php if($row->pickup != null): ?>
                    <?php echo e(date('d/m/Y g:i A',strtotime($row->pickup))); ?>

                    <?php endif; ?>
                    </td>
                    <td>
                    <?php if($row->dropoff != null): ?>
                    <?php echo e(date('d/m/Y g:i A',strtotime($row->dropoff))); ?>

                    <?php endif; ?>
                    </td>
                    <td><?php echo e($row->pickup_addr); ?></td>
                    <td><?php echo e($row->dest_addr); ?></td>
                    <td><?php echo e($row->travellers); ?></td>
                  </tr>
                  <?php endif; ?>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
              </table>
            </div>
          </div>

          <div class="tab-pane" id="upcoming">
            <h4><?php echo app('translator')->getFromJson('menu.my_bookings'); ?></h4>
            <div class="table-responsive">
              <table class="table driver_table">
                <thead class="thead-inverse">
                  <tr>
                    <th><?php echo app('translator')->getFromJson('fleet.customer'); ?></th>
                    <th><?php echo app('translator')->getFromJson('fleet.vehicle'); ?></th>
                    <th><?php echo app('translator')->getFromJson('fleet.pickup'); ?></th>
                    <th><?php echo app('translator')->getFromJson('fleet.dropoff'); ?></th>
                    <th><?php echo app('translator')->getFromJson('fleet.pickup_addr'); ?></th>
                    <th><?php echo app('translator')->getFromJson('fleet.dropoff_addr'); ?></th>
                    <th><?php echo app('translator')->getFromJson('fleet.passengers'); ?></th>
                  </tr>
                </thead>
                <tbody>
                  <?php $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <?php if($row->getMeta('ride_status') == "Upcoming" || $row->status != 1): ?>
                  <tr>
                    <td><?php echo e($row->customer->name); ?></td>
                    <td><?php echo e($row->vehicle['make']); ?> - <?php echo e($row->vehicle['model']); ?> - <?php echo e($row->vehicle['license_plate']); ?></td>
                    <td>
                    <?php if($row->pickup != null): ?>
                    <?php echo e(date('d/m/Y g:i A',strtotime($row->pickup))); ?>

                    <?php endif; ?>
                    </td>
                    <td>
                    <?php if($row->dropoff != null): ?>
                    <?php echo e(date('d/m/Y g:i A',strtotime($row->dropoff))); ?>

                    <?php endif; ?>
                    </td>
                    <td><?php echo e($row->pickup_addr); ?></td>
                    <td><?php echo e($row->dest_addr); ?></td>
                    <td><?php echo e($row->travellers); ?></td>
                  </tr>
                  <?php endif; ?>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script type="text/javascript">
$('.driver_table').DataTable({
  "language": {
      "url": '<?php echo e(__("fleet.datatable_lang")); ?>',
   }
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\oawonusi.HQ\Documents\ICS\FleetManagerv4\resources\views/drivers/profile.blade.php ENDPATH**/ ?>