$.getJSON('js/CAH.json', function(d)
{
  let spread = 'ppf',
      spreads = {
        'ppf': ['Your past', 'Your present', 'Your future'],
        'soa': ['The situation', 'The obstacle', 'Some advice'],
        'ytr': ['You', 'Them', 'Relationship']
      };

  let rand_number_between = function(min, max) {
  	return Math.floor(Math.random() * (max - min + 1)) + min;
  };
  let rand_arr = function(arr) {
  	if(!arr || arr.length === 0) return '';
  	return arr[rand_number_between(0, arr.length - 1)];
  };

  let entityMap = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;',
    '/': '&#x2F;',
    '`': '&#x60;',
    '=': '&#x3D;'
  };
  let escapeHtml = function(str) {
    return String(str).replace(/[&<>"'`=\/]/g, function (s) {
      return entityMap[s];
    });
  }

  let picked = [];
  let pick_random_white = function(tries)
  {
    try {
      let deck_id = rand_arr(Object.keys(d));
      let deck = d[deck_id];
      let card = rand_arr(deck.white);

      if(card.text.length > 130 || picked.includes(card.text))
      {
        pick_random_white(0);
      }
      if(card.pack === undefined || card.id === undefined || card.text === undefined)
      {
        pick_random_white(0);
      }
      else
      {
        picked.push(card.text);
        return {text: card.text, deck: deck.name, pack: card.pack, id: card.id};
      }
    } catch(e) {
      if(tries < 3){
        return pick_random_white(tries + 1);
      } else {
        return false;
      }
    }
  }

  let share_id = '';
  let generate_card = function(meaning, i, pick)
  {
    let card = '<div class="card_wrap"><div class="card" id="card_' + i + '">'
              + '<div class="meaning">' + escapeHtml(meaning) + '</div>'
              + '<div class="text_wrap"><div class="text">' + escapeHtml(pick.text) + '</div></div>'
              + '<div class="deck">' + escapeHtml(pick.deck) + '</div>'
              + '</div></div>';

    $('#spread').append(card);
  }

  let get_cards = function(id)
  {
    let s = id.slice(0, 3);
    let c = id.slice(3);
    let cards = c.split('g');

    if(!spreads[s] || cards.length !== spreads[s].length)
    {
      console.error('invalid spread', s, cards, spreads[s]);
      return false;
    }

    $('#spread_select').val(s);

    for(let i = 0; i < cards.length; i++)
    {
      let card = cards[i];
      let card_arr = card.split('h');
      let deck_id = parseInt(card_arr[0], 16);
      let card_id = parseInt(card_arr[1], 16);
      let pick = null;

      if(!d[deck_id])
      {
        console.error('invalid deck', deck_id);
        return false;
        break;
      }

      for(var j = 0; j < d[deck_id].white.length; j++)
      {
        if(d[deck_id].white[j].id === card_id)
        {
          pick = d[deck_id].white[j];
          break;
        }
      }

      if(pick === null)
      {
        console.error('invalid card', card_id);
        return false;
        break;
      } else {
        pick.deck = d[deck_id].name;
      }

      console.log(pick);
      generate_card(spreads[s][i], i, pick);
    }

    return true;
  }

  $(document).ready(function()
  {
    let url_regex = new RegExp('\\?s=(' + Object.keys(spreads).join('|') + ')', 'i');
    if(location.href.match(url_regex))
    {
      share_id = location.href.split('?s=')[1];
    }

    $('#spread_it').on('click', function()
    {

      if(share_id) location.href = site_url;

      $('#spread').empty();
      picked = [];
      share_id = '';
      spread = $('#spread_select option:selected').val();
      spreads[spread].forEach(function(meaning, i)
      {
        let pick = pick_random_white(0);

        //pick = {text: '', deck: ''};
        share_id = share_id + (i > 0 ? 'g' : '') + pick.pack.toString(16) + 'h' + pick.id.toString(16);
        generate_card(meaning, i, pick);
      });
    })

    if(!share_id)
    {
      $('#spread_it').click();
    }
    else
    {
      if(!get_cards(share_id))
      {
        alert('Invalid Share URL.');
        location.href = site_url;
      }
    }

    $('#spread_select').on('change', function()
    {
      share_id = '';
    })

    $('#share_url').bind({
        copy : function(){
            $('#share_url').fadeOut();
        }
    }).prop("readonly", true);


    $('#share_it').on('click', function(e)
    {
      if (navigator.share)
      {
        navigator.share({
            title: 'Cards Against Humanity Tarot | z0m.bi',
            url: site_url + '?s=' + spread + share_id
          }).then(() => {
            console.log('Shared.');
          }).catch(console.error);
      }
      else
      {
        $('#share_url').val(site_url + '?s=' + spread + share_id)
          .css('display', 'flex').focus().select();
      }
    });
  });
});
