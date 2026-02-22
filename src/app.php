<!DOCTYPE html>
<html lang="en">

<head>
    <!-- css -->
    <?php
    include('../layout/head.php');
    include('../layout/css.php');
    ?>
</head>

<body>

<div class="app-wrapper">
    <!-- app loader -->
    <div class="loader-wrapper">
        <div class="loader_16"></div>
    </div>

    <?php
    include('../layout/sidebar.php');
    ?>
<script>
document.addEventListener("DOMContentLoaded", () => {
    const token = localStorage.getItem("token");
    const userRaw = localStorage.getItem("user");

    console.log("TOKEN:", token);
    console.log("USER RAW:", userRaw);

    if (userRaw) {
        try {
            const user = JSON.parse(userRaw);
            console.log("USER PARSED:", user);
        } catch (e) {
            console.error("User JSON corrupted");
        }
    }



});
</script>


<script>
document.addEventListener("DOMContentLoaded", () => {
    const token = localStorage.getItem("token");
    const user = localStorage.getItem("user");

    if (!token || !user) {
        window.location.replace("login.php");
        return;
    }

    // opcional: marcar sesión activa
    console.log("Session OK");
});
</script>

    <div class="app-content">
        <!-- header -->
        <?php
        include('../layout/header.php');
        ?>

        <!-- main section -->
        <main>
            <div class="container-fluid">
                <!-- Breadcrumb start -->
                <div class="row m-1">
                    <div class="col-12 ">
                        <h4 class="main-title">Time tracking</h4>
                        <ul class="app-line-breadcrumbs mb-3">
                            <li class="">
                                <a href="#" class="f-s-14 f-w-500">
                      <span>
                        <i class="ph-duotone  ph-newspaper f-s-16"></i> Other Pages
                      </span>
                                </a>
                            </li>
                            <li class="active">
                                <a href="#" class="f-s-14 f-w-500">Blank</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <!-- Breadcrumb end -->

                <!-- Blank start -->
                <div class="row">
                    <!-- Default Card start -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 >Welcome, <span data-bind="employee.first_name"></span> <span data-bind="employee.last_name"></span></h5>
                            </div>
                            <div class="card-body">
                                <div id="active-shift-info" class="alert alert-info" style="display:none;">
    </div>
                                <div id="active-shift-info" class="mb-3" style="display:none;"></div>
                                <form class="shifts-form">
                                    <div class="row" id="shift-form-location">
                                        
                                        <div class="col-md-12 floating-select">
                                            <div class="mb-3">
                                                <label class="form-label">Work Location</label>
                                                <select class="form-select">
                                                    <option selected="">select Your Location</option>
                                                    <option value="1">Forest Ridge Apartments</option>
                                                    <option value="2">Big Garages LLC</option>
                                                    <option value="3">Ray's House</option>
                                                    
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row js-shift-date">

                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label">Check-in Date</label>
                                                <input type="date" class="form-control" id="shift-date" >
                                            </div>
                                        </div>
    
          
                                    </div>
                                    

                                    <div class="row js-clock-in" id="clock-in-section">

                                        <div class="col-md-12">
                                   <div class="mb-3">
                                       <label class="form-label">Clock In Time</label>
                                       <input type="time" class="form-control" id="shift-clock-in" >
                                   </div>
                               </div>
                                    </div>

                                    <div class="row js-clock-out">

                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label">Clock Out Time</label>
                                                <input type="time" class="form-control" >
                                            </div>
                                        </div>

                                    </div>
                                    
                                    <div class="row">
                                            <div class="text-end">
                                                <button class="btn btn-primary">Submit</button>
                                                <button type="reset" class="btn btn-secondary">Reset</button>
                                            </div>
                                        </div>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Default Card end -->
                </div>
                <!-- Blank end -->
            </div>
        </main>
    </div>

    <!-- tap on top -->
    <div class="go-top">
      <span class="progress-value">
        <i class="ti ti-arrow-up"></i>
      </span>
    </div>

    <!-- footer -->
    <?php
    include('../layout/footer.php');
    ?>
</div>

<!--customizer-->
<div id="customizer"></div>

</body>

<!-- Javascript -->
<?php
include('../layout/script.php');
?>

</html>
