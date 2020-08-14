<?php $__env->startSection("breadcrumb"); ?>
<li class="breadcrumb-item"><a href="#"><?php echo app('translator')->getFromJson('menu.reports'); ?></a></li>
<li class="breadcrumb-item active"><?php echo app('translator')->getFromJson('fleet.yearlyReport'); ?></li>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
  <div class="col-md-12">
    <div class="card card-info">
      <div class="card-header">
        <h3 class="card-title"><?php echo app('translator')->getFromJson('fleet.yearlyReport'); ?>
        </h3>
      </div>

      <div class="card-body">
        <?php echo Form::open(['route' => 'dreports.yearly','method'=>'post','class'=>'form-inline']); ?>


        <div class="form-group" style="margin-right: 10px">
          <?php echo Form::label('year', __('fleet.year'), ['class' => 'form-label']); ?>

          <?php echo Form::select('year', $years, $year_select,['class'=>'form-control']);; ?>

        </div>

        <div class="form-group" style="margin-right: 10px">
          <?php echo Form::label('vehicle', __('fleet.vehicles'), ['class' => 'form-label']); ?>

          <select id="vehicle_id" name="vehicle_id" class="form-control vehicles" style="width: 250px">
            <option value=""><?php echo app('translator')->getFromJson('fleet.selectVehicle'); ?></option>
            <?php $__currentLoopData = $vehicles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vehicle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($vehicle->id); ?>" <?php if($vehicle->id == $vehicle_select): ?> selected
            <?php endif; ?>><?php echo e($vehicle->make); ?>-<?php echo e($vehicle->model); ?>-<?php echo e($vehicle->license_plate); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <button type="submit" class="btn btn-info"><?php echo app('translator')->getFromJson('fleet.search'); ?></button>
        <?php echo Form::close(); ?>

      </div>
    </div>
  </div>
</div>

<?php if(isset($result)): ?>
<div class="row">
  <div class="col-md-12">
    <div class="card card-info">
      <div class="card-header with-border">
        <h3 class="card-title">
          <?php echo app('translator')->getFromJson('fleet.report'); ?>
        </h3>
      </div>

      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <div class="card card-warning">
              <div class="card-header">
                <h3 class="card-title">Chart - <?php echo app('translator')->getFromJson('fleet.income'); ?></h3></div>
              <div class="card-body">
                <canvas id="canvas1" width="400" height="300"></canvas>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table table-bordered table-striped table-hover myTable">
                <?php ($income_amt = (is_null($income[0]->income) ? 0 : $income[0]->income)); ?>
                <?php ($expense_amt = (is_null($expenses[0]->expense) ? 0 : $expenses[0]->expense)); ?>
                <thead>
                  <tr>
                    <th scope="row"><?php echo app('translator')->getFromJson('fleet.pl'); ?></th>
                    <td><strong><?php echo e(Hyvikk::get("currency")); ?><?php echo e($income_amt-$expense_amt); ?></strong></td>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <th scope="row"><?php echo app('translator')->getFromJson('fleet.income'); ?></th>
                    <td><?php echo e(Hyvikk::get("currency")); ?><?php echo e($income_amt); ?></td>
                    </tr>
                    <tr>
                    <th scope="row"><?php echo app('translator')->getFromJson('fleet.expenses'); ?></th>
                    <td><?php echo e(Hyvikk::get("currency")); ?><?php echo e($expense_amt); ?></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card card-warning">
              <div class="card-header">
                <h3 class="card-title">Chart - <?php echo app('translator')->getFromJson('fleet.expensesByCategory'); ?></h3>
              </div>
              <div class="card-body">
                <canvas id="canvas3" width="400" height="300"></canvas>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table table-bordered table-striped table-hover expTable">
                <thead>
                  <tr>
                    <?php ($tot = 0); ?>
                    <?php $__currentLoopData = $expense_by_cat; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php ($tot = $tot + $exp->expense); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <th scope="row"><?php echo app('translator')->getFromJson('fleet.expensesByCategory'); ?></th>
                    <td><strong><?php echo e(Hyvikk::get("currency")); ?><?php echo e($tot); ?></strong></td>
                  </tr>
                </thead>
                <tbody>
                  <?php $__currentLoopData = $expense_by_cat; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <tr>
                    <th scope="row">
                    <?php if($exp->type == "s"): ?>
                    <?php echo e($service[$exp->expense_type]); ?>

                    <?php else: ?>
                    <?php echo e($expense_cats[$exp->expense_type]); ?>

                    <?php endif; ?>
                    </th>
                    <td><?php echo e(Hyvikk::get("currency")); ?><?php echo e($exp->expense); ?></td>
                  </tr>
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
<?php endif; ?>

<div class="row">
  <div class="col-md-12">
    <div class="card card-default">
      <div class="card-header with-border">
        <h3 class="card-title"><?php echo app('translator')->getFromJson('fleet.yearly_chart'); ?></h3>
      </div>
      <div class="card-body">
        <?php ($useragent = $_SERVER['HTTP_USER_AGENT']); ?>
        <?php if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4))): ?>
          <?php ($height="600"); ?>
        <?php else: ?>
          <?php ($height="300"); ?>
        <?php endif; ?>
          <canvas id="yearly" width="800" height="<?php echo e($height); ?>"></canvas>
      </div>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
<script type="text/javascript" src="<?php echo e(asset('assets/js/cdn/jszip.min.js')); ?>"></script>
<script type="text/javascript" src="<?php echo e(asset('assets/js/cdn/pdfmake.min.js')); ?>"></script>
<script type="text/javascript" src="<?php echo e(asset('assets/js/cdn/vfs_fonts.js')); ?>"></script>
<script type="text/javascript" src="<?php echo e(asset('assets/js/cdn/buttons.html5.min.js')); ?>"></script>
<script type="text/javascript">
  $('#vehicle_id').select2({placeholder: "<?php echo app('translator')->getFromJson('fleet.selectVehicle'); ?>"});

  $(document).ready(function() {
    $('#vehicle_id').select2();
    $('.myTable').DataTable({
      "paging":   false,
      "searching": false,
      "info":     false,
      "ordering": false,
      dom: 'Bfrtip',
      buttons: [{
           extend: 'collection',
              text: 'Export',
              buttons: [
                  'copy',
                  'excel',
                  'csv',
                  'pdf',
              ]}
      ],

      "language": {
               "url": '<?php echo e(__("fleet.datatable_lang")); ?>',
            }
    });

    $('.expTable').DataTable({
      "ordering": false,
      "searching": false,
      "info":     false,
      "pageLength": 5,
      dom: 'Bfrtip',
      buttons: [{
           extend: 'collection',
              text: 'Export',
              buttons: [
                  'copy',
                  'excel',
                  'csv',
                  'pdf',
              ]}
      ],
      "language": {
               "url": '<?php echo e(__("fleet.datatable_lang")); ?>',
            }
    });

  });
</script>
<?php $__env->stopSection(); ?>
<?php $__env->startSection("script2"); ?>

<script src="<?php echo e(asset('assets/js/cdn/Chart.bundle.min.js')); ?>"></script>
<script>
  window.chartColors = {
    red: 'rgb(255, 99, 132)',
    orange: 'rgb(255, 159, 64)',
    yellow: 'rgb(255, 205, 86)',
    green: 'rgb(75, 192, 192)',
    blue: 'rgb(54, 162, 235)',
    purple: 'rgb(153, 102, 255)',
    grey: 'rgb(201, 203, 207)',
    black: 'rgb(0,0,0)'
  };

  function random_color(i){
    var color1,color2,color3;
    var col_arr=[];
    for(x=0;x<=i;x++){

    var c1 = [176,255,84,220,134,66,238];
    var c2 = [254,61,147,114,51,26,137];
    var c3 = [27,111,153,93,157,216,187,44,243];
    color1 = c1[Math.floor(Math.random()*c1.length)];
    color2 = c2[Math.floor(Math.random()*c2.length)];
    color3 = c3[Math.floor(Math.random()*c3.length)];

    col_arr.push("rgba("+color1+","+color2+","+color3+",0.5)");
    }
    // console.log(col_arr);
    return col_arr;
  }

  var chartData = {
    labels: ["<?php echo app('translator')->getFromJson('fleet.income'); ?>", "<?php echo app('translator')->getFromJson('fleet.expenses'); ?>"],
    datasets: [{
        type: 'pie',
        label: '',
        backgroundColor: [window.chartColors.green,window.chartColors.red],
        borderColor: window.chartColors.black,
        borderWidth: 1,
        data: [<?php echo e(@$income_amt); ?>,<?php echo e(@$expense_amt); ?>]
    }]
  };

  var chartData3 = {
    labels: [<?php $__currentLoopData = $expense_by_cat; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> "<?php if($exp->type == "s"): ?><?php echo e($service[$exp->expense_type]); ?> <?php else: ?><?php echo e($expense_cats[$exp->expense_type]); ?><?php endif; ?>", <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>],
    datasets: [{
        type: 'pie',
        label: '',
        backgroundColor: random_color(<?php echo e(count($expense_by_cat)); ?>),
        borderColor: window.chartColors.black,
        borderWidth: 1,
        data: [<?php $__currentLoopData = $expense_by_cat; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($exp->expense); ?>, <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>]
    }]
  };

  var MONTHS = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
  var config = {
      type: 'line',
      data: {
          labels: MONTHS,
          datasets: [{
              label: "<?php echo app('translator')->getFromJson('fleet.expense'); ?>",
              backgroundColor: '#ff5462',
              borderColor: '#ff5462',
              data: [<?php echo e($yearly_expense); ?>],
              fill: false,
          }, {
              label: "<?php echo app('translator')->getFromJson('fleet.income'); ?>",
              fill: false,
              backgroundColor: '#21bc6c',
              borderColor: '#21bc6c',
              data: [<?php echo e($yearly_income); ?>],
          }]
      },
      options: {
          responsive: true,
          title:{
              display:false,
          },
          tooltips: {
              mode: 'index',
              intersect: false,
          },
          hover: {
              mode: 'nearest',
              intersect: true
          },
          scales: {
              xAxes: [{
                  display: true,
                  scaleLabel: {
                      display: true,
                      labelString: "<?php echo app('translator')->getFromJson('fleet.month'); ?>"
                  }
              }],
              yAxes: [{
                  display: true,
                  scaleLabel: {
                      display: true,
                      labelString: "<?php echo app('translator')->getFromJson('fleet.amount'); ?>"
                  }
              }]
          }
      }
  };

  window.onload = function() {
    var ctx = document.getElementById("canvas1").getContext("2d");
    window.myMixedChart = new Chart(ctx, {
        type: 'pie',
        data: chartData,
        options: {

            responsive: true,
            title: {
                display: false,
                text: "<?php echo app('translator')->getFromJson('fleet.chart'); ?>"
            },
            tooltips: {
                mode: 'index',
                intersect: true
            }
        }
    });
             var ctx = document.getElementById("canvas3").getContext("2d");
    window.myMixedChart = new Chart(ctx, {
        type: 'pie',
        data: chartData3,
        options: {

            responsive: true,
            title: {
                display: false,
                text: "<?php echo app('translator')->getFromJson('fleet.chart'); ?>"
            },
            tooltips: {
                mode: 'index',
                intersect: true
            }
        }
    });

    var ctx1 = document.getElementById("yearly").getContext("2d");
    window.myLine = new Chart(ctx1, config);
  };

</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\oawonusi.HQ\Documents\ICS\FleetManagerv4\resources\views/drivers/yearly.blade.php ENDPATH**/ ?>