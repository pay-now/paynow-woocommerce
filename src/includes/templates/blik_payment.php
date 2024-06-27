<div class="paynow-blik-white-label">
    <div class="row">
        <label for="paynow_blik_code" class="col-md-4 form-control-label">
            <?php echo __( 'Enter the BLIK code:', 'pay-by-paynow-pl' ); ?>
        </label>
        <div class="col-md-4">
            <input autocomplete="off" inputmode="numeric" pattern="[0-9]{3} [0-9]{3}" minlength="6" maxlength="6"
                   id="paynow_blik_code" name="authorizationCode" type="text" value="" class="required form-control"
                   placeholder="___ ___">
        </div>
    </div>
    <?php include('data_processing_info.php'); ?>
</div>
