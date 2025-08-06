jQuery(document).ready(function($) {

  $('.account-details-save-button').on('click' ,function() {
    const id = $(this).attr("data-account-id");
    const form = $(`#account-${id}-details-modal-form`);

    $.ajax({
      url: rmiap_admin_params.ajax_url,
      type: 'POST',
      data: {
        action: 'rmiap_account_update',
        form: form.serialize(),
        nonce: rmiap_admin_params.nonce
      },
      success: function(response) {
        if (response.success && response.data) {
          // Chiudo modale e aggiorno la pagina
          $(`#account-${id}-details-modal button[data-bs-dismiss="modal"]`).trigger('click');
          window.location.reload();
        } else {
          //modalBody.html('<p>' + (response.data.message || 'Errore nel caricamento dei dettagli.') + '</p>');
        }
      },
      error: function(xhr, status, error) {
          console.error("AJAX Error:", status, error, xhr.responseText);
          //modalBody.html('<p>Errore nella richiesta AJAX. Controlla la console.</p>');
      }
    });
  });

/*
  $('.account-details-button').on('click' ,function() {
    const id = $(this).attr("data-entry-id");

    $.ajax({
      url: rmiap_admin_params.ajax_url,
      type: 'POST',
      data: {
        action: 'rmiap_entry_confirm',
        id: id,
        nonce: rmiap_admin_params.nonce
      },
      success: function(response) {
        if (response.success && response.data) {
          // Chiudo modale e aggiorno la pagina
          $('#account-details-close-button').click();
          window.location.reload();
        } else {
          //modalBody.html('<p>' + (response.data.message || 'Errore nel caricamento dei dettagli.') + '</p>');
        }
      },
      error: function(xhr, status, error) {
          console.error("AJAX Error:", status, error, xhr.responseText);
          //modalBody.html('<p>Errore nella richiesta AJAX. Controlla la console.</p>');
      }
    });
  });
*/
  $('.entry-confirm-button').on('click' ,function() {
    const id = $(this).attr("data-entry-id");

    $.ajax({
      url: rmiap_admin_params.ajax_url,
      type: 'POST',
      data: {
        action: 'rmiap_entry_confirm',
        id: id,
        nonce: rmiap_admin_params.nonce
      },
      success: function(response) {
        if (response.success && response.data) {
          // Chiudo modale e aggiorno la pagina
          //$('#account-details-close-button').click();
          window.location.reload();
        } else {
          //modalBody.html('<p>' + (response.data.message || 'Errore nel caricamento dei dettagli.') + '</p>');
        }
      },
      error: function(xhr, status, error) {
          console.error("AJAX Error:", status, error, xhr.responseText);
          //modalBody.html('<p>Errore nella richiesta AJAX. Controlla la console.</p>');
      }
    });
  });

  $('#entry-add-button').on('click' ,function() {
    const form = $(`#entry-add-modal-form`);

    $.ajax({
      url: rmiap_admin_params.ajax_url,
      type: 'POST',
      data: {
        action: 'rmiap_entry_confirm',
        form: form.serialize(),
        nonce: rmiap_admin_params.nonce
      },
      success: function(response) {
        if (response.success && response.data) {
          // Chiudo modale e aggiorno la pagina
          $('#entry-add-close-button').click();
          window.location.reload();
        } else {
          //modalBody.html('<p>' + (response.data.message || 'Errore nel caricamento dei dettagli.') + '</p>');
        }
      },
      error: function(xhr, status, error) {
          console.error("AJAX Error:", status, error, xhr.responseText);
          //modalBody.html('<p>Errore nella richiesta AJAX. Controlla la console.</p>');
      }
    });
  });
  
  $('#entries-export-button').on('click' ,function() {

    $.ajax({
      url: rmiap_admin_params.ajax_url,
      type: 'POST',
      data: {
        action: 'rmiap_entries_export',
        //form: form.serialize(),
        nonce: rmiap_admin_params.nonce
      },
      success: function(response) {
        if (response.success && response.data) {
          // Chiudo modale e aggiorno la pagina
          $('#entry-add-close-button').click();
          window.location.reload();
        } else {
          //modalBody.html('<p>' + (response.data.message || 'Errore nel caricamento dei dettagli.') + '</p>');
        }
      },
      error: function(xhr, status, error) {
          console.error("AJAX Error:", status, error, xhr.responseText);
          //modalBody.html('<p>Errore nella richiesta AJAX. Controlla la console.</p>');
      }
    });
  });

  document.querySelector('#race-details-modal')?.addEventListener('show.bs.modal', event => {
    const raceId = event.relatedTarget.attributes.getNamedItem('data-race-id')?.value

    if (raceId) {
      $('#race-details-modal-label').text('Modifica gara')

      $.ajax({
        url: rmiap_admin_params.ajax_url,
        type: 'GET',
        data: {
          action: 'rmiap_race_get',
          id: raceId,
          nonce: rmiap_admin_params.nonce
        },
        success: function(response) {
          if (response.success && response.data) {
            const race = response.data;
            $("input[name='id']").val(race.id);
            $("input[name='name']").val(race.name);
            $("input[name='slug']").val(race.slug);
            $("input[name='date']").val(race.date);
            $("input[name='price']").val(race.price);
            $("input[name='status']").val(race.status);
            $("input[name='account_id']").val(race.account_id);
            $("input[name='start_sale_date']").val(race.start_sale_date);
            $("input[name='end_sale_date']").val(race.end_sale_date);
          } else {
            event.preventDefault();
            //modalBody.html('<p>' + (response.data.message || 'Errore nel caricamento dei dettagli.') + '</p>');
          }
        },
        error: function(xhr, status, error) {
          console.error("AJAX Error:", status, error, xhr.responseText);
          event.preventDefault();
            //modalBody.html('<p>Errore nella richiesta AJAX. Controlla la console.</p>');
        }
      });
    } else {
      $('#race-details-modal-label').text('Nuova gara')
      $("input[name='id']").val('');
      $("input[name='name']").val('');
      $("input[name='slug']").val('');
      $("input[name='date']").val('');
      $("input[name='price']").val('');
      $("input[name='status']").val('');
      $("input[name='account_id']").val('');
      $("input[name='start_sale_date']").val('');
      $("input[name='end_sale_date']").val('');
    }
  })

  $('.race-details-save-button').on('click' ,function() {
    //const id = $(this).attr("data-race-id");
    const form = $(`#race-details-modal-form`);
    console.log(form.serialize())

    $.ajax({
      url: rmiap_admin_params.ajax_url,
      type: 'POST',
      data: {
        action: 'rmiap_race_update',
        form: form.serialize(),
        nonce: rmiap_admin_params.nonce
      },
      success: function(response) {
        if (response.success && response.data) {
          console.log(response.data)
          // Chiudo modale e aggiorno la pagina
          $(`#race-details-modal button[data-bs-dismiss="modal"]`).trigger('click');
          window.location.reload();
        } else {
          //modalBody.html('<p>' + (response.data.message || 'Errore nel caricamento dei dettagli.') + '</p>');
        }
      },
      error: function(xhr, status, error) {
          console.error("AJAX Error:", status, error, xhr.responseText);
          //modalBody.html('<p>Errore nella richiesta AJAX. Controlla la console.</p>');
      }
    });
  });


});
