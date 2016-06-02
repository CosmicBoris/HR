    var nInterval, searchTimer, currentPage;
    function showInspections(id)
    {
        if (id=="0")
        {
          document.getElementById("sIns").innerHTML="";
          return;
        }
        if (window.XMLHttpRequest)
            {
                xmlhttp = new XMLHttpRequest();                  //  новые браузеры 
            } else {
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");    //  древние IE 5...
        }
        xmlhttp.onreadystatechange = function() 
        {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                    document.getElementById("sIns").innerHTML = xmlhttp.responseText;
            }
        };
        xmlhttp.open("GET","/register/GetInspections/"+id, true);
        xmlhttp.send();
    }
    function GetAnswer(str, callBackElementId)
    {
        if (window.XMLHttpRequest)
        {
            xmlhttp = new XMLHttpRequest();                      //  новые браузеры
        } else {
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");    //  древние IE 5...
        }
        xmlhttp.onreadystatechange = function()
        {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                document.getElementById(callBackElementId).innerHTML = xmlhttp.responseText;
                loadingScreen(false);
            }
        };
        loadingScreen(true);
        xmlhttp.open("GET", str, true);
        xmlhttp.send();
    }
    // str - request to, fCallBack - func to call after data retrieved
    function GetJsonResponse(str, fCallBack)
    {
        var req;
        if (window.XMLHttpRequest) {
            req = new XMLHttpRequest();                      //  новые браузеры
        } else {
            req = new ActiveXObject("Microsoft.XMLHTTP");    //  древние IE 5...
        }
        req.onreadystatechange = function()
        {
            if (req.readyState == 4 && req.status == 200) {
                fCallBack(JSON.parse(req.responseText));
            }
        };

        req.open("GET", str, true);
        req.send();
    }
    function CheckEmail()
    {
      var str = this.value;
      if(str=='')
        return;
      
      if (window.XMLHttpRequest)
         {
            xmlhttp = new XMLHttpRequest();                      //  новые браузеры 
        } else {
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");    //  древние IE 5...
        }
        xmlhttp.onreadystatechange = function() 
        {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                document.getElementById("pMail").innerHTML = xmlhttp.responseText;
            }
        };
        xmlhttp.open("GET","/register/CheckEmail/"+str, true);
        xmlhttp.send();
    }
    function CheckLogin()
    {
        var str = this.value;
        if(str=='')
            return;

        if (window.XMLHttpRequest)
        {
            xmlhttp = new XMLHttpRequest();                      //  новые браузеры
        } else {
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");    //  древние IE 5...
        }
        xmlhttp.onreadystatechange = function()
        {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                document.getElementById("pLogin").innerHTML = xmlhttp.responseText;
            }
        };
        xmlhttp.open("GET","/register/CheckLogin/"+str, true);
        xmlhttp.send();
    }
    function  CheckPassword()
    {
        var pOne = document.getElementById('tPass').value;
        var pTwo = document.getElementById('tPassConf').value;
        if(pOne == "" || pTwo == ""){
            document.getElementById('passInfoConf').innerHTML = '';
            return false;
        }
        if(pOne==pTwo){
            document.getElementById('passInfoConf').innerHTML = '';
            return true;
        }
        document.getElementById('passInfoConf').innerHTML = 'Паролі не збігаються!';
        return false;
    }
    function SubForm(form)
    {
        var pError = document.getElementById("passInfo");

        if(form.tPass.value.length < 6 ) {
            pError.innerHTML = "Пароль має бути не менше 6 символів...";
            return;
        }
        if(!CheckPassword()) return;

        var p = document.createElement("input");
        form.appendChild(p);
        p.name = "Password";
        p.type = "hidden";
        p.value = CryptoJS.MD5(form.tPass.value);

        form.tPass.value = '';
        form.submit();
        return true;
    }
	function SubLogin(form)
    {
        var pError = document.getElementById("passInfo");
        var p = document.createElement("input");
        form.appendChild(p);
        p.name = "Password";
        p.type = "hidden";
        p.value = CryptoJS.MD5(form.tPass.value);
 
        form.submit();
        return true;
    }
	function SwitchSubmit(e)
    {
		var l = document.getElementById("tLogin").value;
		var p = document.getElementById("tPass").value;

		if(l != '' && p != ''){
			document.getElementById("bSub").disabled = false;
            e = e || window.event;
            if (e.keyCode == 13)
            {
                document.getElementById("bSub").click();
            }
        }
		else
			document.getElementById("bSub").disabled = true;
    }
    function Test(e)
    {
        alert("Event");
        alert(e.id);
    }
    function Reject(e)
    {
        $.ajax({
            method: "GET",
            dataType: "json",
            url: '/super/reject/'+e.id
        }).done(function(data) {
            if(data['status'] == 1) {
                var tr = $(e).closest('tr');
                tr.fadeOut(600, function(){
                    tr.remove();
                });
                setTimeout(function(){GetAnswer(currentPage, 'wrap')}, 1000);
            }
        }).fail(function() {
            alert("error");
        });
    }
    function Accept(e)
    {
        $.ajax({
            method: "GET",
            dataType: "json",
            url: '/super/approve/'+e.id
        }).done(function(data) {
            if(data['status'] == 1) {
                var tr = $(e).closest('tr');
                tr.fadeOut(600, function(){
                    tr.remove();
                });
                GetAnswer(currentPage, 'wrap');
            }
        }).fail(function() {
            alert("error");
        });
    }
    function loadingScreen(nState)
    {
        clearTimeout(nInterval);
        if(nState) {
            nInterval = setTimeout(function(){
                AddClass("fsc", "overlay-open");
                $(".overlay").toggleClass("on", nState);
            }, 500);
        }else{
            $(".overlay").toggleClass("on", nState);
            RemoveClass("fsc", "overlay-open");
        }
    }

    function JsonSearchResult(e)
    {
        var to = $(e).attr('data-action');
        var what = $('#searchbox').val();
            $.ajax({
                type: "GET",
                dataType: "json",
                data: {search : what},
                url: to})
                .done(function(data){
                    if(data['heading']) {
                        $(".pull-right>.pagination").detach();
                        $("#conTable").after(data['pagination']);
                        $("h3.panel-title").text(data['heading']);
                        $(".table>tbody").html(data['table']);
                    }
                });
    }

    function ShowEdit(user)
    {
        if(user.UserID != null){
            $('#tL').val(user.UserLogin);
            $( "input[name='tPib']" ).val(user.UserFullName);
            $( "input[name='tMail']").val(user.UserEmail);
            //TODO
            $('#sTU').val(user.RegionID).trigger("onchange");
            $('#sIns').val(user.InspectionID);
            $('#sJob').val(user.permissionID);
            $('#ModalEditUser').modal('show');

        } else {
            alert("Помилка на сервері");
        }
    }
    function UpdateBadges(resp)
    {
        $('#uAwaitsc').html(resp.Awaits);
        $( "#uAllc" ).html(resp.All);
    }

    function EntityInfo(element, edrpou)
    {
        $.ajax({
            type: 'GET',
            url: "workspace/Entity",
            data: {id: edrpou}
        }).done(function(data){
            var cb = $(element).find("input[type=checkbox]");
            if(data.Entity_DRFO == 1){
                cb.prop('checked', true);
            } else {
                cb.prop('checked', false);
            }
            for(var key in data){
                $(element).find("input[name*='"+key+"']" ).val(data[key]);
            }
            $(element).modal('show');
        });
    }

    /*highlight selected menu item*/
    $('.workSpaceMenu li').click(function(){
        $('.workSpaceMenu li').removeClass('cbp-vicurrent');
        $(this).addClass('cbp-vicurrent');
        $("#menu").toggleClass("open", false);
        $("#nav-icon3").toggleClass('open', false);
        if(!this.getAttribute('data-action')){return;}
        currentPage = this.getAttribute('data-action');
        GetAnswer(currentPage, 'wrap');
    });
    $('.panel').on('click','.pageAct', function(e){
        if($("#searchbox").length && $("#searchbox").val().length > 2){
            JsonSearchResult(this);
        }else{
            currentPage = this.getAttribute('data-action');
            GetAnswer(currentPage, 'wrap');
        }
    });
    // search
    $("#wrap").on("keyup", "#searchbox", function(e) {
        clearTimeout(searchTimer);
        switch(e.which) {
            case 16:
            case 17:
            case 18:
                return;
        }
        if($(this).val().length > 2){
            var par = this;
            setTimeout(function(){JsonSearchResult(par)},700);
        } else if($(this).val().length == 0){
            GetAnswer($(this).attr('data-action'), "wrap");
        }
    }).on('click', '.table > tbody > tr', function(){
        /*var row = $(this).closest('.table').find('tr')
            .children('td').not(this)
            .css({ backgroundColor: '#ddd' });
        setTimeout(function () {
                $(row)
                    .animate({ paddingTop: 0, paddingBottom: 2 }, 200)
                    .wrapInner('<div />')
                    .children()
                    .slideUp(200);
            }, 350
        );*/
    });
    //open popup
    $('.popupbox-trigger').on('click', function(event) {
        event.preventDefault();
        $('.popupbox').addClass('open');
    });
    //close popup
    $('.popupbox').on('click', function(event) {
        if( $(event.target).is('.popupbox-close') || $(event.target).is('.popupbox')
            || $(event.target).is('#pub_no')) {
            event.preventDefault();
            $(this).removeClass('open');
        }
    });
    //close popup when clicking the esc keyboard button
    $(document).keyup(function(event){
        if(event.which=='27'){
            $('.popupbox').removeClass('open');
        }
    });
    function SetParams()
    {
        var el = this;
        var arg = {};
        arg[$(this).attr('data-key')] = $(this).is(':checked');
        $.ajax({
            type: "POST",
            dataType: "json",
            data: arg,
            url: $(this).attr('data-action'),
            success: function(data){
                if(data.status == 1) {
                    $(el).toggleClass("checked");
                    setTimeout(function(){GetAnswer( $(el).attr("data-postaction"), 'wrap')}, 500);
                }
            }
        });
    }