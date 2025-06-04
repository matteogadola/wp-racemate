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
          $(`#account-${id}-details-modal button[data-bs-dismiss="modal"]`).click();
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

  $('.race-details-save-button').on('click' ,function() {
    const id = $(this).attr("data-race-id");
    const form = $(`#race-${id}-details-modal-form`);

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
          // Chiudo modale e aggiorno la pagina
          $(`#race-${id}-details-modal button[data-bs-dismiss="modal"]`).click();
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
  


});
