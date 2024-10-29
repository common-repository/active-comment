jQuery(document).ready(function($) {
	//tabs
    $('.active-options-tab-btns li').click(function(){
        var tab_id = $(this).attr('data-tab');
        $('.active-options-tab-btns li').removeClass('active-active');
        $('.active-tab-frame').removeClass('active-active');
        $(this).addClass('active-active');
        $("#"+tab_id).addClass('active-active');
    });
    
    $('.active-user-registration').click(function(){
        if($('.active-user-registration:checked').length > 0){
            $('.registration_property').show();
        }else{
            $('.registration_property').hide();
        }
    });
});