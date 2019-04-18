$( document ).ready(function() {

  $(".form_datetime.start-form").datetimepicker({
    pickTime: false,
    minView: 2,
    format: 'yyyy-mm-dd',
    autoclose: true,
    todayBtn:true,
  });

  $(".form_datetime.end-form").datetimepicker({
    pickTime: false,
    minView: 2,
    format: 'yyyy-mm-dd',
    autoclose: true,
    todayBtn:true,
  });

  $(document).on("click",".btn-caching",function () {
    let _form = $(this).parent().serializeArray();
    let _data = {};
    _data.projects=[];
    _form.forEach((o)=>{
      if(o.name==='startDate')_data.startDate=o.value
        else if(o.name==='endDate')_data.endDate=o.value
          else _data.projects.push(o.value)
    });
    if(_data.startDate===''||_data.endDate===''){
      alert('input date for caching')
    }else if(_data.projects.length === 0){
      alert('select projects for caching')
    }else{
      $.ajax({
        type: "POST",
        url: 'index.php?act=caching',
        data: _data
      }).done(function(res) {
        if(res.total&&res.limit){
          for(let i=res.total; i >0; i--) {
            _data.offset=i;
            _data.limit=res.limit;
            $.ajax({
              type: "POST",
              url: 'index.php?act=caching',
              data: _data
            }).done(function(o) { if(o)console.log('caching finish'); });
          }
        }
      })
    }
  })

  $(document).on("click",".btn-export",function () { 
    let _form = $(this).parent();
    let date = _form.find('input[name="startDate"]').val();
    !date?alert('select startDate for export that month'):null;
    $.post('index.php?act=export',{date},(o)=>{
      console.log(o);
    })
  })
});