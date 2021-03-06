<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-gallimage" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_form; ?></h3>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-gallimage" class="form-horizontal">
        <ul class="nav nav-tabs">
            <li class="active"><a href="#tab-general" data-toggle="tab"><?php echo $tab_general; ?></a></li>
            <li><a href="#tab-data" data-toggle="tab"><?php echo $tab_data; ?></a></li>
            <li><a href="#tab-gallery" data-toggle="tab"><?php echo $tab_gallery; ?></a></li>
            <li><a href="#tab-design" data-toggle="tab"><?php echo $tab_design; ?></a></li>
          </ul>
        <div class="tab-content">  
         <div class="tab-pane fade in active" id="tab-general">
              <ul class="nav nav-tabs" id="language">
                <?php foreach ($languages as $language) { ?>
                <li><a href="#language<?php echo $language['language_id']; ?>" data-toggle="tab"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a></li>
                <?php } ?>
              </ul>
              <div class="tab-content">
              <?php foreach ($languages as $language) { ?>
                <div class="tab-pane" id="language<?php echo $language['language_id']; ?>">
                  <div class="form-group required">
                    <label class="col-sm-2 control-label" for="input-name<?php echo $language['language_id']; ?>"><?php echo $entry_name; ?></label>
                    <div class="col-sm-10">
                      <input type="text" name="gallimage_description[<?php echo $language['language_id']; ?>][name]" value="<?php echo isset($gallimage_description[$language['language_id']]) ? $gallimage_description[$language['language_id']]['name'] : ''; ?>" placeholder="<?php echo $entry_name; ?>" id="input-name<?php echo $language['language_id']; ?>" class="form-control" onkeyup="$('#input-meta-title<?php echo $language['language_id']; ?>').val(this.value)" />
                      <?php if (isset($error_name[$language['language_id']])) { ?>
                      <div class="text-danger"><?php echo $error_name[$language['language_id']]; ?></div>
                      <?php } ?>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label" for="input-description<?php echo $language['language_id']; ?>"><?php echo $entry_description; ?></label>
                    <div class="col-sm-10">
                      <textarea name="gallimage_description[<?php echo $language['language_id']; ?>][description]" placeholder="<?php echo $entry_description; ?>" id="input-description<?php echo $language['language_id']; ?>" class="form-control"><?php echo isset($gallimage_description[$language['language_id']]) ? $gallimage_description[$language['language_id']]['description'] : ''; ?></textarea>
                    </div>
                  </div>
                  <div class="form-group required">
                    <label class="col-sm-2 control-label" for="input-meta-title<?php echo $language['language_id']; ?>"><?php echo $entry_meta_title; ?></label>
                    <div class="col-sm-10">
                      <input type="text" name="gallimage_description[<?php echo $language['language_id']; ?>][meta_title]" value="<?php echo isset($gallimage_description[$language['language_id']]) ? $gallimage_description[$language['language_id']]['meta_title'] : ''; ?>" placeholder="<?php echo $entry_meta_title; ?>" id="input-meta-title<?php echo $language['language_id']; ?>" class="form-control" />
                      <?php if (isset($error_meta_title[$language['language_id']])) { ?>
                      <div class="text-danger"><?php echo $error_meta_title[$language['language_id']]; ?></div>
                      <?php } ?>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label" for="input-meta-description<?php echo $language['language_id']; ?>"><?php echo $entry_meta_description; ?></label>
                    <div class="col-sm-10">
                      <textarea name="gallimage_description[<?php echo $language['language_id']; ?>][meta_description]" rows="5" placeholder="<?php echo $entry_meta_description; ?>" id="input-meta-description<?php echo $language['language_id']; ?>" class="form-control"><?php echo isset($gallimage_description[$language['language_id']]) ? $gallimage_description[$language['language_id']]['meta_description'] : ''; ?></textarea>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label" for="input-meta-keyword<?php echo $language['language_id']; ?>"><?php echo $entry_meta_keyword; ?></label>
                    <div class="col-sm-10">
                      <textarea name="gallimage_description[<?php echo $language['language_id']; ?>][meta_keyword]" rows="5" placeholder="<?php echo $entry_meta_keyword; ?>" id="input-meta-keyword<?php echo $language['language_id']; ?>" class="form-control"><?php echo isset($gallimage_description[$language['language_id']]) ? $gallimage_description[$language['language_id']]['meta_keyword'] : ''; ?></textarea>
                    </div>
                  </div>
                </div>
                <?php } ?>
                </div>
                </div>
          <div class="tab-pane fade" id="tab-data">  
            <div class="form-group">
              <label class="col-sm-2 control-label" for="input-category"><span data-toggle="tooltip" title="<?php echo $help_category; ?>"><?php echo $entry_category; ?></span></label>
              <div class="col-sm-10">
                <input type="text" name="gallimage_category" value="" placeholder="<?php echo $entry_category; ?>" id="input-category" class="form-control" />
                <div id="gallimage-category" class="well well-sm" style="height: 150px; overflow: auto;">
                  <?php foreach ($gallimage_categories as $gallimage_category) { ?>
                  <div id="gallimage-category<?php echo $gallimage_category['category_id']; ?>"><i class="fa fa-minus-circle"></i> <?php echo $gallimage_category['name']; ?>
                    <input type="hidden" name="gallimage_category[]" value="<?php echo $gallimage_category['category_id']; ?>" />
                  </div>
                  <?php } ?>
                </div>
              </div>
            </div>    
            <div class="form-group">
               <label class="col-sm-2 control-label"><?php echo $entry_image; ?></label>
               <div class="col-sm-10"><a href="" id="thumb-image" data-toggle="image" class="img-thumbnail"><img src="<?php echo $thumb; ?>" alt="" title="" data-placeholder="<?php echo $placeholder; ?>" /></a>
               <input type="hidden" name="image" value="<?php echo $image; ?>" id="input-image" />
               </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $entry_store; ?></label>
                <div class="col-sm-10">
                  <div class="well well-sm" style="height: 150px; overflow: auto;">
                    <div class="checkbox">
                      <label>
                        <?php if (in_array(0, $gallimage_store)) { ?>
                        <input type="checkbox" name="gallimage_store[]" value="0" checked="checked" />
                        <?php echo $text_default; ?>
                        <?php } else { ?>
                        <input type="checkbox" name="gallimage_store[]" value="0" />
                        <?php echo $text_default; ?>
                        <?php } ?>
                      </label>
                    </div>
                    <?php foreach ($stores as $store) { ?>
                    <div class="checkbox">
                      <label>
                        <?php if (in_array($store['store_id'], $gallimage_store)) { ?>
                        <input type="checkbox" name="gallimage_store[]" value="<?php echo $store['store_id']; ?>" checked="checked" />
                        <?php echo $store['name']; ?>
                        <?php } else { ?>
                        <input type="checkbox" name="gallimage_store[]" value="<?php echo $store['store_id']; ?>" />
                        <?php echo $store['name']; ?>
                        <?php } ?>
                      </label>
                    </div>
                    <?php } ?>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-keyword"><span data-toggle="tooltip" title="<?php echo $help_keyword; ?>"><?php echo $entry_keyword; ?></span></label>
                <div class="col-sm-10">
                  <input type="text" name="keyword" value="<?php echo $keyword; ?>" placeholder="<?php echo $entry_keyword; ?>" id="input-keyword" class="form-control" />
                </div>
              </div>
            <div class="form-group">
                <label class="col-sm-2 control-label" for="input-sort-order"><?php echo $entry_sort_order; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="sort_order" value="<?php echo $sort_order; ?>" placeholder="<?php echo $entry_sort_order; ?>" id="input-sort-order" class="form-control" />
                </div>
              </div>    
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
            <div class="col-sm-10">
              <select name="status" id="input-status" class="form-control">
                <?php if ($status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          </div>
          <div class="tab-pane fade" id="tab-gallery">
          <div class="form-group">
          <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_gsize; ?></label>
          <div class="col-sm-5">
          <input type="text" name="gwidth" value="<?php echo $gwidth; ?>" placeholder="<?php echo $entry_width; ?>" class="form-control" />
          </div>
          <div class="col-sm-5">
          <input type="text" name="gheight" value="<?php echo $gheight; ?>" placeholder="<?php echo $entry_height; ?>" class="form-control" />
          </div>
          </div>
          <div class="form-group">
          <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_psize; ?></label>
          <div class="col-sm-5">
          <input type="text" name="pwidth" value="<?php echo $pwidth; ?>" placeholder="<?php echo $entry_width; ?>" class="form-control" />
          </div>
          <div class="col-sm-5">
          <input type="text" name="pheight" value="<?php echo $pheight; ?>" placeholder="<?php echo $entry_height; ?>" class="form-control" />
          </div>
          </div>
          <table id="images" class="table table-striped table-bordered table-hover">
            <thead>
              <tr>
                <td class="text-left"><?php echo $entry_title; ?></td>
                <td class="text-left"><?php echo $entry_link; ?></td>
                <td class="text-left"><?php echo $entry_image; ?></td>
                <td class="text-right"><?php echo $entry_sort_order; ?></td>
                <td></td>
              </tr>
            </thead>
            <tbody>
              <?php $image_row = 0; ?>
              <?php foreach ($gallimage_images as $gallimage_image) { ?>
              <tr id="image-row<?php echo $image_row; ?>">
                <td class="text-left"><?php foreach ($languages as $language) { ?>
                  <div class="input-group pull-left"><span class="input-group-addon"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> </span>
                    <input type="text" name="gallimage_image[<?php echo $image_row; ?>][gallimage_image_description][<?php echo $language['language_id']; ?>][title]" value="<?php echo isset($gallimage_image['gallimage_image_description'][$language['language_id']]) ? $gallimage_image['gallimage_image_description'][$language['language_id']]['title'] : ''; ?>" placeholder="<?php echo $entry_title; ?>" class="form-control" />
                  </div>
                  <?php if (isset($error_gallimage_image[$image_row][$language['language_id']])) { ?>
                  <div class="text-danger"><?php echo $error_gallimage_image[$image_row][$language['language_id']]; ?></div>
                  <?php } ?>
                  <?php } ?></td>
                <td class="text-left"><input type="text" name="gallimage_image[<?php echo $image_row; ?>][link]" value="<?php echo $gallimage_image['link']; ?>" placeholder="<?php echo $entry_link; ?>" class="form-control" /></td>
                <td class="text-left"><a href="" id="thumb-image<?php echo $image_row; ?>" data-toggle="image" class="img-thumbnail"><img src="<?php echo $gallimage_image['thumb']; ?>" alt="" title="" data-placeholder="<?php echo $placeholder; ?>" /></a>
                  <input type="hidden" name="gallimage_image[<?php echo $image_row; ?>][image]" value="<?php echo $gallimage_image['image']; ?>" id="input-image<?php echo $image_row; ?>" /></td>
                <td class="text-right"><input type="text" name="gallimage_image[<?php echo $image_row; ?>][sort_order]" value="<?php echo $gallimage_image['sort_order']; ?>" placeholder="<?php echo $entry_sort_order; ?>" class="form-control" /></td>
                <td class="text-left"><button type="button" onclick="$('#image-row<?php echo $image_row; ?>').remove();" data-toggle="tooltip" title="<?php echo $button_remove; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
              </tr>
              <?php $image_row++; ?>
              <?php } ?>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="4"></td>
                <td class="text-left"><button type="button" onclick="addImage();" data-toggle="tooltip" title="<?php echo $button_gallimage_add; ?>" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button></td>
              </tr>
            </tfoot>
          </table>
          </div>
          <div class="tab-pane fade" id="tab-design">
            <div class="table-responsive">
              <table class="table table-bordered table-hover">
                <thead>
                  <tr>
                    <td class="text-left"><?php echo $entry_store; ?></td>
                    <td class="text-left"><?php echo $entry_layout; ?></td>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td class="text-left"><?php echo $text_default; ?></td>
                    <td class="text-left"><select name="gallery_layout[0]" class="form-control">
                        <option value=""></option>
                        <?php foreach ($layouts as $layout) { ?>
                        <?php if (isset($gallery_layout[0]) && $gallery_layout[0] == $layout['layout_id']) { ?>
                        <option value="<?php echo $layout['layout_id']; ?>" selected="selected"><?php echo $layout['name']; ?></option>
                        <?php } else { ?>
                        <option value="<?php echo $layout['layout_id']; ?>"><?php echo $layout['name']; ?></option>
                        <?php } ?>
                        <?php } ?>
                      </select></td>
                  </tr>
                  <?php foreach ($stores as $store) { ?>
                  <tr>
                    <td class="text-left"><?php echo $store['name']; ?></td>
                    <td class="text-left"><select name="gallery_layout[<?php echo $store['store_id']; ?>]" class="form-control">
                        <option value=""></option>
                        <?php foreach ($layouts as $layout) { ?>
                        <?php if (isset($gallery_layout[$store['store_id']]) && $gallery_layout[$store['store_id']] == $layout['layout_id']) { ?>
                        <option value="<?php echo $layout['layout_id']; ?>" selected="selected"><?php echo $layout['name']; ?></option>
                        <?php } else { ?>
                        <option value="<?php echo $layout['layout_id']; ?>"><?php echo $layout['name']; ?></option>
                        <?php } ?>
                        <?php } ?>
                      </select></td>
                  </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
          </div>
        </form>
      </div>
    </div>
  </div>
<script type="text/javascript" src="view/javascript/ckeditor/ckeditor.js"></script>
  <script type="text/javascript"><!--
<?php foreach ($languages as $language) { ?>

CKEDITOR.replace('input-description<?php echo $language['language_id']; ?>', {height: 300});
CKEDITOR.on('dialogDefinition', function (event)
        {
            var editor = event.editor;
            var dialogDefinition = event.data.definition;
            var dialogName = event.data.name;

            var tabCount = dialogDefinition.contents.length;
            for (var i = 0; i < tabCount; i++) {
                var browseButton = dialogDefinition.contents[i].get('browse');

                if (browseButton !== null) {
                    browseButton.hidden = false;
                    browseButton.onClick = function() {
						$('#modal-image').remove();
							
							var element = this;
							
							console.log(element.domId);

							var target = $("#"+element.domId).parents('.cke_dialog_ui_vbox').find('input').attr("id");
							console.log(target);


							var fm = $('<div/>').dialogelfinder({
							url : 'index.php?route=common/filemanager/connector&token=' + getURLVar('token'),
								lang : 'en',
								
								destroyOnClose : true,
							      uiOptions : {toolbar : [['home', 'back', 'forward'],['reload'],['mkdir', 'upload'],['open', 'download', 'getfile'],['info'],['quicklook'],['copy', 'cut', 'paste'],['rm'],['duplicate', 'rename', 'edit', 'resize'],['extract', 'archive','multiupload', 'sort'],['search'],['view'],['help']]},		
							     
							      contextmenu: {navbar: ["open", "|", "copy", "cut", "paste", "duplicate", "|", "rm", "|", "info"],cwd: ["reload", "back", "|", "upload", "mkdir", "mkfile", "paste", "|", "sort", "|", "info"],files: ["getfile", "|", "open", "quicklook", "|", "download", "|", "copy", "cut", "paste", "duplicate", "|", "rm", "|", "edit", "rename", "resize", "|", "archive","multiupload", "extract", "|", "info"]},				
								  getFileCallback : function(files, fm) {
							      console.log(files);
							      $('#'+target).val(files.url);
								},
								commandsOptions : {
									getfile : {
										oncomplete : 'close',
										folders : false
									}
								}
							}).dialogelfinder('instance'); 
							$('.dialogelfinder').css({'z-index':'99999999'});


											
					}
                }
            }
        });

$('#input-description___').attr({
	height: 300
});
<?php } ?>
//--></script>
<script type="text/javascript"><!--
// Category
$('input[name=\'gallimage_category\']').autocomplete({
  'source': function(request, response) {
    $.ajax({
      url: 'index.php?route=catalog/gallimage_category/autocomplete&token=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request),
      dataType: 'json',     
      success: function(json) {
        response($.map(json, function(item) {
          return {
            label: item['name'],
            value: item['category_id']
          }
        }));
      }
    });
  },
  'select': function(item) {
    $('input[name=\'gallimage_category\']').val('');
    
    $('#gallimage-category' + item['value']).remove();
    
    $('#gallimage-category').append('<div id="gallimage-category' + item['value'] + '"><i class="fa fa-minus-circle"></i> ' + item['label'] + '<input type="hidden" name="gallimage_category[]" value="' + item['value'] + '" /></div>'); 
  }
});

$('#gallimage-category').delegate('.fa-minus-circle', 'click', function() {
  $(this).parent().remove();
});
//--></script> 
  <script type="text/javascript"><!--
var image_row = <?php echo $image_row; ?>;

function addImage() {
	html  = '<tr id="image-row' + image_row + '">';
    html += '  <td class="text-left">';
	<?php foreach ($languages as $language) { ?>
	html += '    <div class="input-group">';
	html += '      <span class="input-group-addon"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /></span><input type="text" name="gallimage_image[' + image_row + '][gallimage_image_description][<?php echo $language['language_id']; ?>][title]" value="" placeholder="<?php echo $entry_title; ?>" class="form-control" />';
    html += '    </div>';
	<?php } ?>
	html += '  </td>';	
	html += '  <td class="text-left"><input type="text" name="gallimage_image[' + image_row + '][link]" value="" placeholder="<?php echo $entry_link; ?>" class="form-control" /></td>';	
	html += '  <td class="text-left"><a href="" id="thumb-image' + image_row + '" data-toggle="image" class="img-thumbnail"><img src="<?php echo $placeholder; ?>" alt="" title="" data-placeholder="<?php echo $placeholder; ?>" /></a><input type="hidden" name="gallimage_image[' + image_row + '][image]" value="" id="input-image' + image_row + '" /></td>';
	html += '  <td class="text-right"><input type="text" name="gallimage_image[' + image_row + '][sort_order]" value="" placeholder="<?php echo $entry_sort_order; ?>" class="form-control" /></td>';
	html += '  <td class="text-left"><button type="button" onclick="$(\'#image-row' + image_row  + '\').remove();" data-toggle="tooltip" title="<?php echo $button_remove; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>';
	html += '</tr>';
	
	$('#images tbody').append(html);
	
	image_row++;
}
//--></script>
<script type="text/javascript"><!--
$('#language a:first').tab('show');
//--></script>
</div>
<?php echo $footer; ?>