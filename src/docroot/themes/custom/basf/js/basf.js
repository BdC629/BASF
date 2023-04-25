(function($){

  Drupal.behaviors.basf = {
    attach: function (context, settings) {

      // Sticky nav bar for environment indicator (normal)
      $('.sticky-nav').sticky({
        topSpacing: 0,
        zIndex: 100,
        stopper: '#footer',
      });

      // Sticky nav bar for environment indicator (normal)
      $('.sticky-nav-env').sticky({
        topSpacing: 33,
        zIndex: 100,
        stopper: '#footer',
      });

      // Sticky nav bar (admin bar available)
      $('.sticky-nav-admin').sticky({
        topSpacing: 30,
        zIndex: 100,
        stopper: '#footer',
      });

      // Sticky nav bar for environment indicator (admin bar available)
      $('.sticky-nav-admin-env').sticky({
        topSpacing: 33,
        zIndex: 100,
        stopper: '#footer',
      });

      // Sticky content
      $('.sticky').sticky({
        topSpacing: 90,
        zIndex: 2,
        stopper: '#footer',
      });

      // Sticky environment indicator
      $('#environment-indicator').sticky({
        stickyClass: 'environment-indicator-sticky',
        zIndex: 100,
        stopper: '#footer',
      });

      // Sticky environment indicator
      $('.admin-menu #environment-indicator').sticky({
        stickyClass: 'environment-indicator-sticky-admin',
        zIndex: 100,
        stopper: '#footer',
      });

      // BASF-181 -"Change password" in case error, does not show the active tab
      setTimeout(function () {
        $('#useredittabcontainer a.nav-link').click(function () {
          var active_tab_id = $(this).attr('id');
          localStorage.setItem('user_edit_active_tab', active_tab_id);
        });

        if (typeof (Storage) !== 'undefined') {
          if (localStorage.user_edit_active_tab) {
            $('#' + localStorage.user_edit_active_tab).click();
          }
        }
      }, 10);

      // BASF-515 Sortable My Apps Table
      if ($('.common-attributes').length > 0) {
        $('.common-attributes').DataTable({
          'searching': false,
          'paging': false,
          'info': false,
        });
        $('.dataTables_length').addClass('bs-select');
      }
    }
  };
})(jQuery);
