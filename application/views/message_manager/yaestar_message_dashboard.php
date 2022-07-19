<link
      rel="stylesheet"
      href="https://kendo.cdn.telerik.com/2022.2.510/styles/kendo.default-ocean-blue.min.css"
    />
    <script src="https://kendo.cdn.telerik.com/2022.2.510/js/jquery.min.js"></script>
    <script src="https://kendo.cdn.telerik.com/2022.2.510/js/kendo.all.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jszip/2.4.0/jszip.min.js"></script>
<section class="section">
  
<div id="example">
      <div id="grid"></div>
      <script>
        $(document).ready(function () {
          
                    var base_url="<?php echo base_url(); ?>";
                    var get_post_conversation_url = 'get_post_conversation_yaestar';
          
                  
$(document).on('click', 'span.k-button-text', function(e){
          // var base_url="<?php echo base_url(); ?>";

  //         console.log
         get_post_conversation_url = 'get_post_conversation_yaestar_all';
  
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
                    personalcontact: { editable: false, nullable: true },
                    src: { editable: false },
                    duration: { editable: false },
                    datetime: { editable: false },
                    // nama: { editable: false },
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
            toolbar: ["excel", "pdf","Waktu" ,"search"],
            columns: [
              {
                field: "personalcontact",
                title: "Nama Personal",
                width: 300
              },
              {
                field: "src",
                title: "Nomer Telepon Pemanggil",
                width: 105
              },
              {
                field: "duration",
                title: "Durasi Telepon",
                width: 105
              },
              {
                field: "datetime",
                title: "Waktu ",
                width: 105
              },
              
            ],
          });

});

$(document).on('click','.k-grid-refresh',function(e){

  window.location.reload();
})
// console.log(get_post_conversation_url);

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
                    personalcontact: { editable: false, nullable: true },
                    src: { editable: false },
                    duration: { editable: false },
                    datetime: { editable: false },
                    // nama: { editable: false },
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
            toolbar: ["excel", "pdf","Menampilkan Semua Data" ,"search","refresh"],
            columns: [
              {
                field: "personalcontact",
                title: "Nama Personal",
                width: 300
              },
              {
                field: "src",
                title: "Nomer Telepon Pemanggil",
                width: 105
              },
              {
                field: "duration",
                title: "Durasi Telepon",
                width: 105
              },
              {
                field: "datetime",
                title: "Waktu ",
                width: 105
              },
              
            ],
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
