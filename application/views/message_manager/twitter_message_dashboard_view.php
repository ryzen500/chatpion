<link
      rel="stylesheet"
      href="https://kendo.cdn.telerik.com/2022.2.510/styles/kendo.default-ocean-blue.min.css"
    />
	
<!-- Sweet alert -->
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Kendo -->
    <script src="https://kendo.cdn.telerik.com/2022.2.510/js/jquery.min.js"></script>
    <script src="https://kendo.cdn.telerik.com/2022.2.510/js/kendo.all.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jszip/2.4.0/jszip.min.js"></script>
<section class="section">
  
<div id="example">
      <div id="grid"></div>
	
<div class="modal fade" id="modaljavascript" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" width="100%">
    <div class="modal-dialog">
        <div class="modal-content">
        	<!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">Modal Javascript</h4>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            </div>
            <!-- Modal body -->
            <div class="modal-body">
            	Mengambil Data terbaru<br>
              <div class="spinner-grow" role="status">
  <span class="sr-only">Loading...</span>
</div>

       		</div>
       		<!-- Modal footer -->
		<div class="modal-footer">
		   <button type="button" class="btn btn-danger" id="closemodal">Close</button>
		</div>
        </div>
    </div>
</div>

      <script>
        $(document).ready(function () {
          
          var base_url="<?php echo base_url(); ?>";
          var get_post_conversation_url = 'get_post_conversation_twitter_view';
            dataSource = new kendo.data.DataSource({
              transport: {
                read: {
                  url:base_url+'message_manager/'+get_post_conversation_url,
                  dataType: "json"
                },
                parameterMap: function (options, operation) {
                  if (operation !== "read" && options.models) {
                    return { models: kendo.stringify(options.models) };
                  }
                }
              },

              batch: true,
              pageSize: 20,
              autoSync: true,
              schema: {
                model: {
                  id: "id",
                  fields: {
                    // pelapor_id: { editable: false, nullable: true },
                    // pesan: { editable: false },
                    // tanggal: { editable: false },
                    // nama: { editable: false },
                    sender_id: { editable: false, nullable: true },
                    recept_id: { editable: false },
                    message: { editable: false },
                    user_balas: { editable: false },
                    datetime: { editable: false },
                    // nik: { type: "number" },
                    // phone: { type: "number" }
                  }
                }
              }
            });

          $("#grid").kendoGrid({
            dataSource: dataSource,
            columnMenu: {
              filterable: false
            },
            height: 680,
            pageable: true,
            sortable: true,
            navigatable: true,
            resizable: true,
            reorderable: true,
            groupable: true,
            filterable: true,
            toolbar: ["excel", "pdf", "search","refresh"],
            columns: [
              {
                field: "sender_id",
                title: "Pengirim ID",
                width: 300
              },
              {
                field: "recept_id",
                title: "Pesan ditujukan kepada User",
                width: 105
              },
              {
                field: "message",
                title: "Pesan",
                width: 105
              },
              {
                field: "datetime",
                title: "Waktu",
                width: 105
              },
              {
                field: "user_balas",
                title: "Pegawai Yang membalas Pesan",
                width: 105
              },

              { template: "<button id='jawab' class='k-button k-button-md k-rounded-md k-button-solid k-button-solid-base customEdit'><span class='k-button-text'>Jawab</span></button>", title:"Action",            
              }
             
            ],
          });
        });


        
        $(document).on('click', '#jawab', function(e){
          var base_url="<?php echo base_url(); ?>";

  // console.log("Hello World");
  window.location.href= base_url + "message_manager/twitter_message_dashboard";
  return false;
});

     
$(document).on('click', '.k-grid-refresh', function(e){
          // var base_url="<?php echo base_url(); ?>";

  // console.log("Hello World");

  $.ajax({
      type:'POST' ,
      url: "<?php echo site_url(); ?>message_manager/reload_data",
      // data:{id:id,page_id:page_id,subscribe_id:subscribe_id,call_from_conversation:'1'},
      success:function(response)
      {
        let timerInterval
Swal.fire({
  title: 'Update Data Messages!',
  html: 'Mengambil Data Terbaru.',
  timer: 60000,
  timerProgressBar: true,
  didOpen: () => {
    Swal.showLoading()
    const b = Swal.getHtmlContainer().querySelector('b')
    // timerInterval = setInterval(() => {
    //   b.textContent = Swal.getTimerLeft()
    // }, 100)
  },
  willClose: () => {
    clearInterval(timerInterval)
  }
}).then((result) => {
  /* Read more about handling dismissals below */
  if (result.dismiss === Swal.DismissReason.timer) {
    // console.log('I was closed by the timer')
    window.location.href= base_url + "message_manager/twitter_message_dashboard_view";
return true;
// location.reload();
}
})

  //      setInterval(() => {

          // window.location.href= base_url + "message_manager/twitter_message_dashboard_view";
  // return false;
  //       }, 15000);
      }
    }); 
});
      </script>

      <style type="text/css">
        .customer-photo {
          display: inline-block;
          width: 32px;
          height: 32px;
          border-radius: 50%;
          background-size: 32px 35px;
          background-position: center center;
          vertical-align: middle;
          line-height: 32px;
          box-shadow: inset 0 0 1px #999, inset 0 0 10px rgba(0, 0, 0, 0.2);
          margin-left: 5px;
        }

        .customer-name {
          display: inline-block;
          vertical-align: middle;
          line-height: 32px;
          padding-left: 3px;
        }

        .k-grid tr .checkbox-align {
          text-align: center;
          vertical-align: middle;
        }

        .product-photo {
          display: inline-block;
          width: 32px;
          height: 32px;
          border-radius: 50%;
          background-size: 32px 35px;
          background-position: center center;
          vertical-align: middle;
          line-height: 32px;
          box-shadow: inset 0 0 1px #999, inset 0 0 10px rgba(0, 0, 0, 0.2);
          margin-right: 5px;
        }

        .product-name {
          display: inline-block;
          vertical-align: middle;
          line-height: 32px;
          padding-left: 3px;
        }

        .k-rating-container .k-rating-item {
          padding: 4px 0;
        }

        .k-rating-container .k-rating-item .k-icon {
          font-size: 16px;
        }

        .dropdown-country-wrap {
          display: flex;
          flex-wrap: nowrap;
          align-items: center;
          white-space: nowrap;
        }

        .dropdown-country-wrap img {
          margin-right: 10px;
        }

        #grid .k-grid-edit-row > td > .k-rating {
          margin-left: 0;
          width: 100%;
        }
      </style>
</section>
