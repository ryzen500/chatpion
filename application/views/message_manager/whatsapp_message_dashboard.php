<link
      rel="stylesheet"
      href="https://kendo.cdn.telerik.com/2022.2.510/styles/kendo.default-ocean-blue.min.css"
    />
    <script src="https://kendo.cdn.telerik.com/2022.2.510/js/jquery.min.js"></script>
    <script src="https://kendo.cdn.telerik.com/2022.2.510/js/kendo.all.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jszip/2.4.0/jszip.min.js"></script>
<section class="section">
  
<div id="example">
      <div id="grid">

      </div>
      <script>
        $(document).ready(function () {
          var crudServiceBaseUrl = "http://lewatwa.disdukcapilsurabaya.id",
            dataSource = new kendo.data.DataSource({
              transport: {
                read: {
                  url: crudServiceBaseUrl + "/api/getUserChatLogAll",
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
                    pelapor_id: { editable: false, nullable: true },
                    pesan: { editable: false },
                    tanggal: { editable: false },
                    nama: { editable: false },
                    nik: { type: "number" },
                    phone: { type: "number" }
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
            toolbar: ["excel", "pdf", "search"],
            columns: [
              {
                field: "nama",
                title: "Nama Pelapor",
                width: 300
              },
              {
                field: "nik",
                title: "NIK",
                width: 105
              },
              {
                field: "phone",
                title: "Phone",
                width: 105
              },
              {
                field: "pesan",
                title: "Pesan",
                width: 105
              },
              {
                field: "tanggal",
                title: "Tanggal",
                width: 105
              },
              { template: "<button id='jawab' class='k-button k-button-md k-rounded-md k-button-solid k-button-solid-base customEdit'><span class='k-button-text'>Jawab</span></button>", title:"Action",            
              }
            ]
          });
        });


        
        $(document).on('click', '#jawab', function(e){
          var base_url="<?php echo base_url(); ?>";

  // console.log("Hello World");
  window.location.href= base_url + "message_manager/whatsapp_message_dashboard_view";
  return false;
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


<script>
  var social_media = 'fb';
  var get_post_conversation_url = 'get_post_conversation_whatsapp';
  var get_pages_conversation_url = 'get_pages_conversation_whatsapp';
  var reply_to_conversation_url = 'reply_to_conversation';
</script>


<?php include(FCPATH.'application/views/message_manager/whats_app_message_dashboard_common_js.php');?>  