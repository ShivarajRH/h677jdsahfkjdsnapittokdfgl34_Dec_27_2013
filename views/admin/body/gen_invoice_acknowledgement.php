<style>
    .leftcont { display: none;}
    table.datagridsort tbody td { padding: 4px; }
    .datagrid td { padding: 1px; }
    .datagrid th { background: #443266;color: #C3C3E5; }
    .subdatagrid {    width: 100%; }
    .subdatagrid th {
        padding: 4px 0 2px 4px !important;
        font-size: 11px !important;
        color: #130C09;
        background-color: rgba(112, 100, 151, 0.51);
    }
    .subdatagrid td {
            /*font-size: 11px !important;*/
            padding: 4px !important;
    }
</style>
<div class="page_wrap container">
	
	<div class="page_topbar" >
		<h2 class="page_title fl_left">Manage Acknowledgements</h2>	
		<div class="page_action_buttons fl_right" align="right">
                    
		</div>
	</div>
	<div style="clear:both">&nbsp;</div>
	<div class="page_content">
            <form action="" name="" id="">
            <table class="datagrid" cellspacing="4" cellpadding="4">
                <tr>
                    <th>Slno</th>
                    <th>Territory Name</th>
                    <th><dfn id=whatwg><abbr title="Territory Manager">TM</abbr></dfn> /
                        <abbr title="Business Executive">BE</abbr>
                    </th>
                    <th><= 9/12/2013 - 11/12/2013</th>
                    <th> 9/12/2013 - 11/12/2013</th>
                    <th> 9/12/2013 - 11/12/2013 =></th>
                    <th><input type="checkbox" name="" id="" class="chk_all_terr_print"></th>
                </tr>
                <tr>
                    <td>1</td>
                    <td>Puttur</td>
                    <td>
                        Mahanthappa H (TM)<br/>
                        Basava (BE)<br/>
                        Sharan (BE)<br/>
                    </td>
                    <td>
                        3 franchise, 50 items, 35,000,00 Rs
                    </td>
                    <td>
                        1 franchise, 50 items, 35,000,00 Rs
                    </td>
                    <td>
                        2 franchise, 50 items, 35,000,00 Rs
                    </td>
                    <td><input type="checkbox" name="" id="" class="chk_terr_print"></td>
                </tr>
                <tr>
                    <td>1</td>
                    <td>Baggur</td>
                    <td>
                        Channappa H (TM)<br/>
                        Chandra (BE)<br/>
                        Basava (BE)<br/>
                        Sharan (BE)<br/>
                    </td>
                    <td>
                        3 franchise, 50 items, 35,000,00 Rs
                    </td>
                    <td>
                        1 franchise, 50 items, 35,000,00 Rs
                    </td>
                    <td>
                        2 franchise, 50 items, 35,000,00 Rs
                    </td>
                    <td><input type="checkbox" name="" id="" class="chk_terr_print"></td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>Davangere</td>
                    <td>
                        Mahanthappa H (TM)<br/>
                        Nagarathna (BE)<br/>
                        Basava (BE)<br/>
                        Sharan (BE)<br/>
                    </td>
                    <td>
                        3 franchise, 50 items, 35,000,00 Rs
                    </td>
                    <td>
                        1 franchise, 50 items, 35,000,00 Rs
                    </td>
                    <td>
                        2 franchise, 50 items, 35,000,00 Rs
                    </td>
                    <td><input type="checkbox" name="" id="" class="chk_terr_print"></td>
                </tr>
                <tr>
                    <td colspan="7"><input type="submit" name="" id="" class="btn_generate_inv"></td>
                </tr>
            </table>
            </form>
	</div>
</div>
<script type="text/javascript">
    $(".chk_all_terr_print").bind("click",function() {
        var elt = $(this);
        if(elt.is(":checked")) {
            $(".chk_terr_print").attr("checked",true);
        }
        else {
            $(".chk_terr_print").attr("checked",false);
        }
    });
    $(".btn_generate_inv").click(function(e){
        e.preventDefault();
        alert( $(".chk_terr_print:checked").length );
    });
</script>