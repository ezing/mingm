$(document).ready(function(){
	
	$("#sub_address").bind("click",function(){
		$("#withdraw form").submit();
	});
	
	$("#verify_image_box").find(".verify_close_btn").bind("click",function(){
        $("#verify_image_box").hide();
    });
	
	
	$("#withdraw form").bind("submit",function(){
		
		var bank_name = $("#withdraw form").find("input[name='bank_name']").val();
		var bank_account = $("#withdraw form").find("input[name='bank_account']").val();
		var bank_user = $("#withdraw form").find("input[name='bank_user']").val();
		var money = $("#withdraw form").find("input[name='money']").val();
		var type = $("#withdraw form").find("select[name='type']").val();
		
		if($.trim(bank_name)==""&&type==1)
		{
			$.showErr("请输入开户行名称");
			return false;
		}
		if($.trim(bank_account)==""&&type==1)
		{
			$.showErr("请输入开户行账号");
			return false;
		}
		if($.trim(bank_user)==""&&type==1)
		{
			$.showErr("请输入开户人真实姓名");
			return false;
		}
		if($.trim(money)==""||isNaN(money)||parseFloat(money)<=0)
		{
			$.showErr("请输入正确的提现金额");
			return false;
		}
		
		var ajax_url = $("#withdraw form").attr("action");
		var query = $("#withdraw form").serialize();
		$.ajax({
			url:ajax_url,
			data:query,
			dataType:"json",
			type:"POST",
			success:function(obj){
				if(obj.status==1){
					$.showSuccess("保存成功",function(){
						location.href = obj.url;
					});
					
				}else if(obj.status==0){
					if(obj.info)
					{
						$.showErr(obj.info,function(){
							if(obj.url)
								location.href = obj.url;
						});
					}
					else
					{
						if(obj.url)
							location.href = obj.url;
					}
					
				}else{
					
				}
			}
		});
		
		
		return false;
	});
	
	$('.bank').hide();
//	$("select[name='type']").bind("change",function(){
//		if($("select[name='type']").val()==1){
//			$('.bank').show();
//		}else{
//			$('.bank').hide();
//		}
	

	//关于手机号的验证码绑定
	init_bind_sms_btn();
	//绑定按钮事件
	init_sms_btn($("#tx_sms_btn"));
	//初始化倒计时
//	function init_sms_btn() {
//		$("#input_content_set").find(".ph_verify_btn[init_sms!='init_sms']").each(function(i, o) {
//			$(o).attr("init_sms", "init_sms");
//			var lesstime = $(o).attr("lesstime");
//			var divbtn = $(o).next();
//			divbtn.attr("lesstime", lesstime);
//			if(parseInt(lesstime) > 0)
//				init_sms_code_btn($(divbtn), lesstime);
//		});
//	}
	
	//关于短信验证码倒计时
	function init_sms_btn(btn)
	{
		$(btn).stopTime();
		$(btn).everyTime(1000,function(){
			lesstime = parseInt($(btn).attr("lesstime"));
			lesstime--;
			$(btn).text("重新获取("+lesstime+")");
			$(btn).attr("lesstime",lesstime);
			if(lesstime<=0)
			{
				$(btn).stopTime();
				$(btn).text("发送验证码");
			}
		});
	}
	
	function init_bind_sms_btn() {
		if(!$("#input_content_set").find(".ph_verify_btn").attr("bindclick")) {
			$("#input_content_set").find(".ph_verify_btn").attr("bindclick", true);
			$("#input_content_set").find(".ph_verify_btn").bind("click", function() {
				
				if($("#tx_sms_btn").attr('lesstime') > 0){
					return false;
				}
				
				if($(this).attr("rel") == "disabled")
					return false;
				var btn = $(this);
				var query = new Object();
				query.act = "send_fxsms_code";
				query.account = 1;
				query.no_verify = 1;
				query.verify_code = (btn).attr("verify_code");
				//发送手机验证登录的验证码
				$.ajax({
					url : AJAX_URL,
					dataType : "json",
					data : query,
					type : "POST",
					global : false,
					success : function(data) {
						if(data.status == 1) {
							init_sms_code_btn(btn, data.lesstime);
							init_sms_btn($("#tx_sms_btn"));
							IS_RUN_CRON = true;							
						}else{
								if(data.status==-1)
								{
									$("#verify_image_box .verify_form_box .verify_content").html("");
                                    var html_str = '<div class="v_input_box"><input type="text" class="v_txt" placeholder="图形码" id="verify_image"/><img src="'+data.verify_image+"&r="+Math.random()+'"  /></div>'+
                                                    '<div class="blank"></div><div class="blank"></div>'+
                                                    '<div class="v_btn_box"><input style="-webkit-appearance: none;"  type="button" class="v_btn" name="confirm_btn" value="确认"/></div>';
                                    $("#verify_image_box .verify_form_box .verify_content").html(html_str);
                                    $("#verify_image_box").show();

									$("#verify_image_box").find("img").bind("click",function(){
										$(this).attr("src",data.verify_image+"&r="+Math.random());
									});
									$("#verify_image_box").find("input[name='confirm_btn']").bind("click",function(){
										var verify_code = $.trim($("#verify_image_box").find("#verify_image").val());
										if(verify_code=="")
										{
											$.showErr("请输入图形验证码");
										}
										else
										{
											$(btn).attr("verify_code",verify_code);
											$("#verify_image_box .verify_form_box .verify_content").html("");
				                            $("#verify_image_box").hide();
										}
									});
									if($(btn).attr("verify_code")&&$(btn).attr("verify_code")!="")
									{
										$.showErr(data.info,function(){
											$(btn).attr("verify_code","")
										});
									}
								}
								else
								{
									$.showErr(data.info);
								}
						}
					}
				});
			});
		}

	}
	
	 
	
});