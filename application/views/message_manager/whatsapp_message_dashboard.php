<link
      rel="stylesheet"
      href="https://kendo.cdn.telerik.com/2022.2.510/styles/kendo.default-ocean-blue.min.css"

    />

    <link rel="stylesheet" href="https://kendo.cdn.telerik.com/2022.2.621/styles/kendo.common.min.css" />
     <link rel="stylesheet" href="https://kendo.cdn.telerik.com/2022.2.621/styles/kendo.default.min.css" />

     
    <link rel="stylesheet" href="https://kendo.cdn.telerik.com/2022.2.621/styles/kendo.default.mobile.min.css" />


    <script src="https://kendo.cdn.telerik.com/2022.2.510/js/jquery.min.js"></script>
    <script src="https://kendo.cdn.telerik.com/2022.2.510/js/kendo.all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/2.4.0/jszip.min.js"></script>
    <style>
        .demo-section ul {

            margin: 0;
            padding: 0; 
        $("span.k-switch").kendoSwitch({
           checked: false        
        });


        console.log($("span.k-switch").attr("aria-checked"));

        }

            .demo-section ul li {
                list-style-type: none;
                margin: 0;
                padding: 10px 10px 10px 20px;
                min-height: 28px;
                line-height: 28px;
                vertical-align: middle;
                border-top: 1px solid rgba(128,128,128,.5);
            }

        .demo-section {
            min-width: 220px;
            margin-top: 50px;
            padding: 0;
        }

            .demo-section ul li .k-switch {
                float: right;
            }

        .settings-head {
            height: 66px;
            background: url('../content/web/switch/settings-title.png') no-repeat 20px 50% #2db245;
        }
    </style>
    <section class="section">
  


<div id="example">
      <div id="grid">

      </div>
      <script>
        $(document).ready(function () {
          
          var base_url="<?php echo base_url(); ?>";
          var get_post_conversation_url = 'get_post_conversation_whatsapp_pelapor';
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
                    id:{type: "number"},
                    nama:{editable:false},
                    nik:{editable:false},
                    phone:{editable:false}
                    
                  }
                }
              }
            });

            // console.log(dataSource);

          $("#grid").kendoGrid({

            detailExpand:function(e){
              var grid = this;      

              childGrid=    e.detailRow.find('[data-role="grid"]').attr('id');
              grid.element.find('.k-master-row').each(function(){

                if(this!= e.masterRow[0]){
                  grid.collapseRow(this);
                }                            

              })                        

            },
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
            toolbar: ["excel","pdf","search"],
            
            columns: [
              {
                field: "id",
                title: "ID Pelapor",
                width: 300,
                hidden:true
              },
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
            
              { template: "<button id='jawab' class='k-button k-button-md k-rounded-md k-button-solid k-button-solid-base customEdit'><span class='k-button-text'>Jawab</span></button>", title:"Action",            
              },
              { field: "status", width: "120px", template: "<input class='customClass' #if (status=='operator') { # checked='checked' # } # type='checkbox' />" }

            ],
            editable: "inline",
            detailInit: detailInit,
            dataBound: function() {
    
              this.tbody.find(".customClass").kendoMobileSwitch({
                offLabel: "BOT",
                onLabel: "HUMAN",
              })
            }
          });


          
          function detailInit(e) {

            var col1 =$(".k-master-row").closest("tr").find("td:eq(1)").html();

            
            console.log(e.data.id);
          var base_url="<?php echo base_url(); ?>";
          var get_post_conversation_url = 'get_post_conversation_whatsapp_view/';
          $("<div id='child"+e.data.id+"'/>").appendTo(e.detailCell).kendoGrid({
            dataSource: {
              transport: {
                read: {
                  url:base_url+'message_manager/'+get_post_conversation_url + e.data.id,
                  dataType: "json"
                }
              },
              serverPaging: true,
              serverSorting: true,
              serverFiltering: true,
              pageSize: 10,
              filter: { field: "pelapor_id", operator: "eq", value: e.data.pelapor_id },
              schema:{
                model:{
                  id:'id'
                }
              }
            },
            persistSelection:true,

            scrollable: false,
            sortable: true,
            pageable: true,
            columns: [
              { selectable: true, width: "50px" },
              { field: "id", width: "110px",hidden:true },
              { field: "code", title:"Responder" },
              { field: "tanggal", title:"Waktu" },
              { field: "pesan", title:"Pesan " },
            ]
          });
        }
        
      });
      
      
      
      
      
      $(document).on('click','.km-switch-on',function(e){
        

  // var col1 =$(".k-grid table tr").find("td").eq(1).html();
  
  var col1 = $(this).closest("tr").find("td:eq(1)").text();
  
  // console.log(col1);
  window.location.href= base_url + "message_manager/updateToggleWAOperator/" + col1 ;
  return false; 
})



$(document).on('click','.km-switch-off',function(e){

  var col1 = $(this).closest("tr").find("td:eq(1)").text();
  
  
  console.log(e);
  window.location.href= base_url + "message_manager/updateToggleWA/" +col1;
  return false;

})
        
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
