<link rel="stylesheet" type="text/css" href="{$estate_folder}/css/jquery.lightbox-0.5.css" media="screen">
<script type="text/javascript" src="{$estate_folder}/js/jquery.lightbox-0.5.js"></script>
<script src="{$estate_folder}/apps/system/js/realtymap.js" type="text/javascript"></script>
<script>
var loc_objects={$geoobjects_collection_clustered};
var map_type='{$map_type}';
</script>

{assign var=x value=array()}
{if $data.city_id.value_string ne ''}
	{append var=x value=$data.city_id.value_string}
{/if}

{if $data.district_id.value_string ne ''}
	{append var=x value=$data.district_id.value_string}
{/if}

{if $data.street_id.value_string ne ''}
	{append var=x value=$data.street_id.value_string}
{/if}

{if $data.number.value ne '' && $data.number.value ne '0'}
	{append var=x value=$data.number.value}
{/if}

{assign var=rname value=$x|implode:', '}


{literal}
<script>
var rname='{/literal}{$rname}{literal}';
$(document).ready(function(){
    var RM=new RealtyMap();
    //use next line instead prev line for using Yandex ver. 2.1 capabilities
    //var RM=new RealtyMap('2.1');
    if(loc_objects.length==0){
    	RM.initGeocoded('property-map', rname, map_type, {defaultZoom: 16, yandexMapType: 'yandex#map'});
    }else{
    	RM.initJSON('property-map', loc_objects, map_type, {defaultZoom: 16, yandexMapType: 'yandex#map'});
    }

});
</script>
{/literal}

<script>
{literal}
$(document).ready(function(){
	InitPropertyCarousel('cproperty_gal');
});
{/literal}
</script>

{*lazy billing panel. apps.billing required*}
{*$fast_billing*}

<div class="row">
    <div class="span9">
		<h1 class="page-header">{$title}</h1>
		{if 1==0}
		{if (int)$data.price.value!=0 && $data.currency_id.value!=0 && isset($_courses[$data.currency_id.value])}
		<ul class="price_block">
		{foreach from=$_courses item=courseone}
			{assign var=price_converted value=$data.price.value*$_courses[$data.currency_id.value].course/$courseone.course}
			<li>{$price_converted}{$courseone.code}</li>
		{/foreach}
		</div>
		{/if}
		{/if}

		<div class="favblock">
		{if isset($smarty.session.favorites)}
            {if in_array($data.id.value, $smarty.session.favorites)}
                <a class="fav-rem" alt="{$data.id.value}" title="{$L_DELETEFROMFAVORITES}" href="#remove_from_favorites"></a>
            {else}
                <a class="fav-add" alt="{$data.id.value}" title="{$L_ADDTOFAVORITES}" href="#add_to_favorites"></a>
            {/if}
        {else}
        	<a class="fav-add" alt="{$data.id.value}" title="{$L_ADDTOFAVORITES}" href="#add_to_favorites"></a>
        {/if}
		{if $apps_pdfreport_enabled==1}
		<a href="?format=pdf" class="btn btn-primary"><i class="icon-white icon-print"></i> Печать</a>
		{/if}

		</div>
               {if $photo|count>0}
                <div class="carousel property" id="cproperty_gal">
                    <div class="preview">
                        <a rel="prettyPhoto[gallery1]" href="{$estate_folder}/img/data/{$photo[0].normal}" class="lbgallery" title="Фото" ><img src="{$estate_folder}/img/data/{$photo[0].normal}" alt=""></a>
                    </div>
                    {if $photo|count>1}
                    <div class="content">
                        <a class="carousel-prev" href="#">Previous</a>
                        <a class="carousel-next" href="#">Next</a>
                        <ul>
                        {section name=j loop=$photo}
                        {if $smarty.section.j.index > 0}
                        <li>
                          <a rel="prettyPhoto[gallery1]" href="{$estate_folder}/img/data/{$photo[j].normal}"><img src="{$estate_folder}/img/data/{$photo[j].normal}" /></a>
                        </li>
                        {/if}
                        {/section}
                        </ul>
                    </div>
                    {/if}
                </div>
                {/if}
                <div class="property-detail">
                	<div class="noverview-holder">
                		<div class="pull-left overview">
                        	<div class="row">
                            	<div class="span3">
                            		<h2>Кратко</h2>
									<table>
		                                 {foreach from=$hvd_tabbed item=tab key=tabname}
		                                     {if $tab|count>0}
		                                         {foreach from=$tab item=data_item}
		
		                                             {if $data_item.type eq "primary_key" or $data_item.value eq "0" or $data_item.value eq "" or $data_item.name eq "currency_id" or $data_item.name eq "export_cian" or $data_item.name eq "user_id" or $data_item.name eq "price"  or $data_item.name eq "youtube" or $data_item.type eq "hidden" or $data_item.name eq "text" or $data_item.type eq "geodata" or $data_item.name eq "meta_keywords"  or $data_item.name eq "meta_description" or $data_item.name eq "meta_title" or $data_item.type eq "uploads" or $data_item.name eq "text_en"}
		
		                                             {elseif $data_item.name eq "fio"}
		                                                 {assign var="agent_fio" value=$data_item.value}
		                                             {elseif $data_item.name eq "phone"}
		                                                 {assign var="agent_phone" value=$data_item.value}
		                                             {elseif $data_item.name eq "email"}
		                                                 {assign var="agent_email" value=$data_item.value}
		                                             {elseif $data_item.type eq "destination"}
		                                                 {if $data_item.value_string!=''}
		                                                     <tr><th>{$data_item.title}</th><td>{$data_item.value_string}</td></tr>
		                                                 {/if}
		                                             {elseif $data_item.type eq "select_by_query"}
		                                                 {if $data_item.value_string!=''}
		                                                     <tr><th>{$data_item.title}</th><td>{$data_item.value_string}</td></tr>
		                                                 {/if}
		                                             {elseif $data_item.type eq "select_box_structure"}
		                                                 {if $data_item.value_string!=''}
		                                                     <tr><th>{$data_item.title}</th><td>{$data_item.value_string}</td></tr>
		                                                 {/if}
		                                             {elseif $data_item.type eq "checkbox"}
		                                                 {if $data_item.name ne 'hot' and $data_item.name ne 'active'}
		                                                     {if $data_item.value eq 1}
		                                                         <tr><th>{$data_item.title}</th><td><input type="checkbox" checked="checked" disabled="disabled" /></td></tr>
		                                                     {/if}
		                                                 {/if}
		                                             {elseif $data_item.type eq "select_box"}
		                                                 {if $data_item.value_string!=''}
		                                                     <tr><th>{$data_item.title}</th><td>{$data_item.value_string}</td></tr>
		                                                 {/if}
		                                             {elseif $data_item.type eq "tlocation"}
		                                                 <tr><th>{$data_item.title}</th><td>{$data_item.tlocation_string}</td></tr>
		                                             {elseif $data_item.type eq "select_by_query_multi" && is_array($data_item.value_string) && !empty($data_item.value_string)}
		                                             	<tr><th>{$data_item.title}</th><td>{$data_item.value_string|print_r}{', '|implode:$data_item.value_string}</td></tr>
		                                             
		                                             {else}
		                                                 {if $data_item.value!=''}
		                                                     {if $data_item.name eq "text"}
		                                                         <tr><th>{$data_item.title}</th><td>{$data_item.value|nl2br}</td></tr>
		                                                     {else}
		                                                         <tr><th>{$data_item.title}</th><td>{if is_array($data_item.value) && !empty($data_item.value)}{$data_item.value|implode:','}{elseif is_array($data_item.value) && empty($data_item.value)}{else}{$data_item.value}{/if}</td></tr>
		                                                     {/if}
		                                                 {/if}
		                                             {/if}
		
		                                         {/foreach}
		
		                                     {/if}
		                                 {/foreach}

										</table>
	                			</div>
	                        </div>
	                    </div>
	                    {if $data.text.value != ''}
                		<div class="noverview-full">{$data.text.value}</div>
                		{/if}
                	</div>

                	{if $data.youtube.value != ''}
                	<div class="noverview-holder">
                		<div class="noverview-full">
							<div align="center">
								<iframe width="560" height="315" src="//www.youtube.com/embed/{$data.youtube.value}" frameborder="0" allowfullscreen></iframe>
								<p>&nbsp;</p>
                   			</div>
                    	</div>
                    </div>
                    {/if}

					<h2>Карта</h2>
					<div id="property-map" data-geo="{$data.geo.value.lat};{$data.geo.value.lng}"></div><!-- /#property-map -->

					{if $apps_reservation_on==1}
						<div class="res" data-id="{$data.id.value}"></div>
						{literal}
						<script>
						$(document).ready(function(){
							var id=$('.res').attr('data-id');
							$('.res').load(estate_folder+'/apps/reservation/js/ajax.php?action=get_reservation_panel&id='+id);
						});
						</script>
						{/literal}
					{/if}
				</div>
				{if $similar_data|count>0}
				<h2>{$L_SIMILAR}</h2>

                <div class="properties-rows">
                    <div class="row">
                        {section name=x loop=$similar_data}
                        <div class="property span9">
                               <div class="row">
                                   <div class="image span3">
                                       <div class="content">
                                           <a href="{$similar_data[x].href}"></a>
                                           {if $similar_data[x].image.image_array|count ne 0}
                                        <img src="{$estate_folder}/img/data/{$similar_data[x].image.image_array[0].preview}" class="previewi">
                                        {else}
                                        <img src="{$estate_folder}/template/frontend/realia/img/no_foto_270x200.png" class="previewi">
                                        {/if}

                                       </div>
                                   </div>

                                   <div class="body span6">
                                       <div class="title-price row">
                                           <div class="title span4">
                                               <h2>
                                                <a href="{$similar_data[x].href}">
                                                {if $similar_data[x].city_id.value_string ne ''} {$similar_data[x].city_id.value_string}{if
                                    $similar_data[x].street_id.value_string ne ''}, {$similar_data[x].street_id.value_string}{if
                                    $similar_data[x].number.value ne ''}, {$similar_data[x].number.value}{/if}{/if}
                                    {else} {if $similar_data[x].street_id.value_string ne ''} {$similar_data[x].street_id.value_string}{if
                                    $similar_data[x].number.value ne ''}, {$similar_data[x].number.value}{/if} {/if}
                                    {/if}
                                                </a>
                                            </h2>
                                           </div>
                                           {if $similar_data[x].price_discount.value > 0}
                  							  <div class="price">
                  							  {$similar_data[x].price_discount.value|number_format:0:",":" "} {if $similar_data[x].currency_id.value_string != ''}{$similar_data[x].currency_id.value_string}{/if}
                   							  <div class="price_discount_list">{$similar_data[x].price.value|number_format:0:",":" "} {if $similar_data[x].currency_id.value_string != ''}{$similar_data[x].currency_id.value_string}{/if}</div>
                   							  </div>
                    					   {else}
                   							 <div class="price">{$similar_data[x].price.value|number_format:0:",":" "} {if $similar_data[x].currency_id.value_string != ''}{$similar_data[x].currency_id.value_string}{/if}</div>
                   						   {/if}

                                       </div>

                                       <div class="location">{$similar_data[x].topic_id.value_string}</div>

                                       <div class="area">
                                           <span class="key">{$L_SQUARE} м<sup>2</sup>:</span>
                                           <span class="value">{$similar_data[x].square_all.value}/{$similar_data[x].square_live.value}/{$similar_data[x].square_kitchen.value}</span>
                                       </div><!-- /.area -->
                                        <div class="area">
                                            <span class="key">{$L_FLOOR}:</span>
                                            <span class="value">{$similar_data[x].floor.value}/{$similar_data[x].floor_count.value}</span>
                                        </div>
                                   </div>
                                </div>
                            </div>
                            {/section}
                        </div>
                    </div>
                    {/if}
			</div>




            <div class="sidebar span3">
            	{if $user_data ne ''}
                <div class="widget our-agents">
                    <div class="title">
                        <h2>Агент</h2>
                    </div>

                    <div class="content">
                        <div class="agent">
                            <div class="image">
                                {if $user_data.imgfile.value != ''}
                                <img src="{$estate_folder}/img/data/user/{$user_data.imgfile.value}" />
                                {else}
                                <img src="{$estate_folder}/template/frontend/{$current_theme_name}/img/userplaceholder.png" />
                                {/if}
                            </div>
                            <div class="name">
                            {if isset($data.fio.value) && $data.fio.value!=''}
                                {$data.fio.value}
                            {else}
                                {$user_data.fio.value}
                            {/if}
                            </div>
                            <div class="phone">
                            {if isset($data.phone.value) && $data.phone.value!=''}
                                {$data.phone.value}
                            {elseif $user_data.phone.value != ''}
                                 {$user_data.phone.value}
                            {/if}
                            </div>
                            {if $user_data.mobile.value != ''}
                            <div class="phone">{$user_data.mobile.value}</div><!-- /.phone -->
                            {/if}

                            {if isset($data.email.value) && $data.email.value!=''}
                            <div class="email"><a href="mailto:{$data.email.value}">{$data.email.value}</a></div>
                            {elseif $user_data.email.value != ''}
                            <div class="email"><a href="mailto:{$user_data.email.value}">{$user_data.email.value}</a></div>
                            {/if}
                            <br />
                            {if $show_upper == 'true'}
                            <br /><span><a class="btn btn-info" href="{$estate_folder}/upper/realty{$data.id.value}"><i class="icon-white icon-chevron-up"></i> {$L_UP_AD}</a></span>
                            {/if}
                            {if $smarty.session.user_id!=$user_data.user_id.value && $mailbox_on==1}
                                {include file=$apps_mailbox_block title_data=[$data.topic_id.value_string,$data.city_id.value_string,$data.street_id.value_string] to=$user_data.user_id.value message_to_author_title=''}
                            {/if}
                        </div>

                   </div>
                </div>
                {/if}
                {include file='right_special.tpl'}
				<br/>
           	</div>
</div>