		var ajaxCall;

		Array.prototype.remove = function(value){
			var index = this.indexOf(value);
			if(index != -1){
				this.splice(index, 1);
			}
			return this;
		};
		function enableTextArea(bool){
			$('#mailpass').attr('disabled', bool);
		}
		function gbrn_liveUp(){
			var count = parseInt($('#acc_live_count').html());
			count++;
			$('#acc_live_count').html(count+'');
		}
		function gbrn_dieUp(){
			var count = parseInt($('#acc_die_count').html());
			count++;
			$('#acc_die_count').html(count+'');
		}
		function gbrn_wrongUp(){
			var count = parseInt($('#wrong_count').html());
			count++;
			$('#wrong_count').html(count+'');
		}
		function gbrn_badUp(){
			var count = parseInt($('#bad_count').html());
			count++;
			$('#bad_count').html(count+'');
		}

		function stopLoading(bool){
			$('#loading').attr('src', 'img/clear.gif');
			var str = $('#checkStatus').html();
			$('#checkStatus').html(str.replace('Checking','Stopped'));
			enableTextArea(false);
			$('#submit').attr('disabled', false);
			$('#stop').attr('disabled', true);
			if(bool){
				alert('Done');
			}else{
				ajaxCall.abort();
			}
		}
		function updateTitle(str){
			document.title = str;
		}
		function updateTextBox(mp){
			var mailpass = $('#mailpass').val().split("\n");
			mailpass.remove(mp);
			$('#mailpass').val(mailpass.join("\n"));
		}
		function GbrnTmfn(lstMP, curMP, delim, cEmail, bank, card, info, no){
			
			if(lstMP.length<1 || curMP>=lstMP.length){
				stopLoading(true);
				return false;
			}
			updateTextBox(lstMP[curMP]);
			ajaxCall = $.ajax({
				url: 'check.php',
				dataType: 'json',
				cache: false,
				type: 'POST',
				beforeSend: function (e) {
					updateTitle('['+no+'/'+lstMP.length+'] DXD-ID Checker');
					$('#checkStatus').html(''+ lstMP[curMP]).effect("highlight", {color:'#00ff00'}, 1000);
					$('#loading').attr('src', 'img/loading.gif');
				},
				data: 'ajax=1&do=check&mailpass='+encodeURIComponent(lstMP[curMP])
						+'&delim='+encodeURIComponent(delim)+'&email='+cEmail+'&bank='+bank+'&card='+card+'&info='+info,
				success: function(data) {
					switch(data.error){
						case -1:
							curMP++;
							$('#wrong').append(data.msg+'<br />');
							gbrn_wrongUp();
							break;
						case 1:
						case 3:
						case 2:
							curMP++;
							$('#acc_die').append(data.msg+'<br />');
							gbrn_dieUp();
							break;
						case 0:
							curMP++;
							$('#acc_live').append(data.msg+'<br />');
							$('#my_balance').text(data.balance);
							gbrn_liveUp();
							break;
					}
					no++;
					GbrnTmfn(lstMP, curMP, delim, cEmail, bank, card, info, no);
				}
			});
			return true;
		}
		function filterMP(mp, delim){
			var mps = mp.split("\n");
			var filtered = new Array();
			var lstMP = new Array();
			for(var i=0;i<mps.length;i++){
				if(mps[i].indexOf('@')!=-1){
					var infoMP = mps[i].split(delim);
					for(var k=0;k<infoMP.length;k++){
						if(infoMP[k].indexOf('@')!=-1){
							var email = $.trim(infoMP[k]);
							var pwd = $.trim(infoMP[k+1]);
							if(filtered.indexOf(email.toLowerCase())==-1){
								filtered.push(email.toLowerCase());
								lstMP.push(email+'|'+pwd);
								break;
							}
						}
					}
				}
			}
			return lstMP;
		}
		function resetResult() {
			$('#acc_die,#wrong').html('');
			$('#acc_die_count,#wrong_count').text(0);
		}
		$(document).ready(function(){
			$('#stop').attr('disabled', true).click(function(){
			  stopLoading(false);  
			});
			$('#submit').click(function(){
				var no = 1;
				var delim = $('#delim').val().trim();
				var mailpass = filterMP($('#mailpass').val(), delim);
				var bank = $('#bank').is(':checked') ? 1 : 0;
				var card = $('#card').is(':checked') ? 1 : 0;
				var info = $('#info').is(':checked') ? 1 : 0;
				var cEmail = $('#email').is(':checked') ? 1 : 0;
				if($('#mailpass').val().trim()==''){
					alert('No Mail/Pass found!');
					return false;
				}
				$('#mailpass').val(mailpass.join("\n")).attr('disabled', true);
				$('#result').show();
				resetResult();
				$('#submit').attr('disabled', true);
				$('#stop').attr('disabled', false);
				GbrnTmfn(mailpass, 0, delim, cEmail, bank, card, info, no);
				return false; 
			});
		});