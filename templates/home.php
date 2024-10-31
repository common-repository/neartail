<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light" style="border-bottom:1px solid #e1e1e1;">
    <a class="navbar-brand" href="#">
        <img src="https://neartail.com/logo.png" class="navbar-icon logo" alt="icon">
        Neartail
    </a>
</nav>
<div class="content">
    <div class="row">
        <div class="col-md-12 col-lg-6 mr-auto ml-auto">
            <h1 style="margin-top:60px;">Take Food Orders In Your Wordpress</h1>
            <p> From restaurants to grocery stores. 2000+ business owners trust us. </p>
            <a href="<?php echo esc_url(admin_url('admin.php?page=embed_google_forms'));?>" class="button">Connect</a>
            
        </div>
    </div>
    <div class="col-md-12 col-lg-6 mr-auto ml-auto" style="margin-top:50px;">
        <div class="animation-content" style="text-align:center;">
            <img src="https://cdn.neartail.com/public/animation/neartail.png" class="lazyAnimate img-fluid hero-image" alt="Google Forms without esign" 
                data-json-src="https://cdn.neartail.com/public/animation/neartail.json">
            <div id="lottie-container" style="display:none"> </div>
        </div>
    </div>
</div>
