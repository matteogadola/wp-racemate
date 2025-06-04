//const clubs = require('../data/names.json');

jQuery(document).ready(function($) {
  const checkoutButton = $('#racemate-form button[name=checkout]');
  const checkoutFailure = $('#racemate-form-error');
  const checkoutSuccess = $('#racemate-form div.alert-success[role="alert"]');
  renderCheckoutButton('stripe');

  $('#racemate-form input[type=radio][name=payment_method]').on('change' ,function() {
    renderCheckoutButton(this.value);
  });

  checkoutButton.on('click' ,function() {
    const form = $('#racemate-form');
    renderCheckoutButton('loading');
    checkoutFailure.hide();

    $.ajax({
      url: rmiap_form_params.ajax_url,
      type: 'POST',
      data: {
          action: 'rmiap_form_checkout',
          form: form.serialize(),
          nonce: rmiap_form_params.nonce
      },
      success: function(response) {
        if (response.success && response.data) {
          if (response.data?.url) {
            window.location.replace(response.data.url);
          } else {
            console.warn('malformed response', response.data);
            //form.get(0).reset();
            //checkoutSuccess.show();
            //window.location.replace(window.location.origin + '?checkout=success');
          }
        } else {
          console.warn('incomplete response', response.data);
        }
      },
      error: function(xhr, status, error) {
        try {
          const { data } = JSON.parse(xhr.responseText);
          checkoutFailure.html(data.message).show();
          //$('#racemate-form p.form-error').show();
        } catch(e) {
          console.error(e.message, xhr.responseText)
        }
      },
      complete: function() {
        renderCheckoutButton();
      },
    });
  });

  function renderCheckoutButton(status) {
    if (status === 'loading') {
      checkoutButton.prop('disabled', true);
      checkoutButton.html('<span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span class="ml-2"><span>Invio...</span>');
    } else {
      checkoutButton.prop('disabled', false);

      if (!status) {
        status = $('#racemate-form input[type=radio][name=payment_method]:checked').val();
      }

      const price = $('#racemate-form input[name=race_price]').val();
      if (status == 'stripe') {
        const newPrice = priceAfterFee(price);

        checkoutButton.html(`Effettua pagamento (${newPrice / 100}€)`);
      } else if (status == 'cash') {
        checkoutButton.html(`Iscriviti (${price / 100}€)`);
      }
    }
  }

  function priceAfterFee(price) {
    const stripeTax = 25 + Math.round(price * 0.015);
    const stripeTaxIva = Math.round(stripeTax * 0.22);
    const stripeFee = Math.ceil((stripeTax + stripeTaxIva) / 50) * 50;
    return parseInt(price) + parseInt(stripeFee);
  };

  $('#racemate-form input[name=club]').on('keyup' ,function() {
    console.log(this.value)
    if (this.value.length <= 3) return;

    /*$.ajax({
      url: rmiap_form_params.ajax_url,
      type: 'POST',
      data: {
        action: 'rmiap_form_clubs',
        value: this.value,
        nonce: rmiap_form_params.nonce
      },
      success: function(response) {
        console.log(response)
        if (response.success && response.data) {
          const options = Object.values(response.data).map(c => `<option value="${c}"></option>`)
          $('#racemate-form datalist[id=clubOptions]').html(options)
        }
      }
    });*/

    $.getJSON("https://teamvaltellina.com/wp-content/plugins/wp-racemate/assets/data/clubs.json", (clubs) => {
      const options = clubs
        .filter(c => c.includes(this.value.toUpperCase()))
        .map(c => `<option value="${c}"></option>`)
      $('#racemate-form datalist[id=clubOptions]').html(options)
    });
  });
});