(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.enum_form = {
    attach: function (context, settings) {
      $('tr.enum-items-table-row').once('enum-item').each(function (i, item) {
        const $item = $(item);
        const $radio = $('input[type="radio"]', $item);
        if($radio.attr('checked')==='checked'){
          $item.addClass('row-selected')
        }
        $item.on('click', function (event) {
          event.preventDefault();
          $radio.prop('checked', 'checked').trigger('change');
        })
      })
    }
  };

})(jQuery, Drupal, drupalSettings);
