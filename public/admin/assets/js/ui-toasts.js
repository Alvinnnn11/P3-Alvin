document.addEventListener("DOMContentLoaded",function(e){let t=document.querySelector(".toast-ex"),s=document.querySelector(".toast-placement-ex"),n=document.querySelector("#showToastAnimation"),o=document.querySelector("#showToastPlacement"),i,c,a,l,r;function d(e){e&&null!==e._element&&(s&&(s.classList.remove(i),DOMTokenList.prototype.remove.apply(s.classList,a)),t&&t.classList.remove(i,c),e.dispose())}n&&(n.onclick=function(){l&&d(l),i=document.querySelector("#selectType").value,c=document.querySelector("#selectAnimation").value,t.classList.add(i,c),(l=new bootstrap.Toast(t)).show()}),o&&(o.onclick=function(){r&&d(r),i=document.querySelector("#selectTypeOpt").value,a=document.querySelector("#selectPlacement").value.split(" "),s.classList.add(i),DOMTokenList.prototype.add.apply(s.classList,a),(r=new bootstrap.Toast(s)).show()});let u=-1;class m extends Notyf{_renderNotification(e){var t=super._renderNotification(e);return e.message&&(t.message.innerHTML=e.message),t}}let p=new m({duration:3e3,ripple:!0,dismissible:!1,position:{x:"right",y:"top"},types:[{type:"info",background:config.colors.info,className:"notyf__info",icon:{className:"icon-base bx bxs-info-circle icon-md text-white",tagName:"i"}},{type:"warning",background:config.colors.warning,className:"notyf__warning",icon:{className:"icon-base bx bxs-error icon-md text-white",tagName:"i"}},{type:"success",background:config.colors.success,className:"notyf__success",icon:{className:"icon-base bx bxs-check-circle icon-md text-white",tagName:"i"}},{type:"error",background:config.colors.danger,className:"notyf__error",icon:{className:"icon-base bx bxs-x-circle icon-md text-white",tagName:"i"}}]});document.getElementById("showNotification").addEventListener("click",()=>{var e=document.getElementById("message").value||(e=["Don't be pushed around by the fears in your mind. Be led by the dreams in your heart.",'<div class="mb-3"><input class="input-small form-control" value="Textbox"/>&nbsp;<a href="http://example.com" target="_blank" class="text-white">This is a hyperlink</a></div><div class="d-flex"><button type="button" id="okBtn" class="btn btn-primary btn-sm me-2">Close me</button><button type="button" id="surpriseBtn" class="btn btn-sm btn-secondary">Surprise me</button></div>',"Live the Life of Your Dreams","Believe in Yourself!","Be mindful. Be grateful. Be positive.","Accept yourself, love yourself!"])[u=(u+1)%e.length],t=document.getElementById("dismissible").checked,s=document.getElementById("ripple").checked,n=document.getElementById("duration").value,n=n?parseInt(n):3e3,o=document.querySelector('input[name="positiony"]:checked').value,o={x:document.querySelector('input[name="positionx"]:checked').value,y:o},n={type:document.querySelector('input[name="notificationType"]:checked').value,message:e,duration:n,dismissible:t,ripple:s,position:o};setTimeout(()=>{var e=document.getElementById("okBtn"),t=document.getElementById("surpriseBtn");e&&e.addEventListener("click",()=>{p.dismissAll()}),t&&t.addEventListener("click",()=>{p.success("Surprise! This is a new message.")})},100),p.open(n)}),document.getElementById("clearNotifications").addEventListener("click",()=>{p.dismissAll()})});