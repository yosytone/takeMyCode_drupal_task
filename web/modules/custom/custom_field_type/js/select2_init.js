(function ($, Drupal, once) {

  Drupal.behaviors.myModuleSelect2Init = {
    attach: function (context) {

      // Фильтруем элементы с классом '.my-feature', чтобы выполнить инициализацию только один раз.
      const elements = once('myModuleSelect2Init', '#mySelect', context);

      // Проверяем, есть ли элементы для инициализации.
      if (elements.length > 0) {
        // Инициализируем select2 только для найденных элементов.
        elements.forEach((el) => {
          $(el).select2({
            ajax: {
              url: 'http://takemc.docksal/get-authors', //
              dataType: 'json',
              delay: 250,
              data: function (params) {
                return {
                  q: params.term, // search term
                  page: params.page
                };
              },

            },
            placeholder: 'Search for a repository',
            minimumInputLength: 1,
          });
        });
      }

    }
  };

})(jQuery, Drupal, once);