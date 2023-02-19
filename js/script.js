$.getJSON('js/cah-filtered.json', function(data)
{
  let spread = 'ppf';
  const spreads = {
        'ppf': ['Your past', 'Your present', 'Your future'],
        'soa': ['The situation', 'The obstacle', 'Some advice'],
        'ytr': ['You', 'Them', 'Relationship']
      };

  const rand_number_between = (min, max) => {
  	return Math.floor(Math.random() * (max - min + 1)) + min;
  };
  const rand_arr = (arr) => {
  	if(!arr || arr.length === 0) return '';
    let rand_id = rand_number_between(0, arr.length - 1);
    let rand_a = arr[rand_id];
    if(typeof(rand_a) === 'object') rand_a.cid = rand_id;
  	return rand_a;
  };

  const entityMap = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;',
    '/': '&#x2F;',
    '`': '&#x60;',
    '=': '&#x3D;'
  };
  const escapeHtml = (str) => {
    return String(str).replace(/[&<>"'`=\/]/g, function (s) {
      return entityMap[s];
    });
  }

  let picked = [];

  const pick_random_white_promise = (tries) =>
  {
    if(tries === undefined) tries = 0;

    return new Promise((resolve, reject) => 
    {
      let pack_id = rand_arr(Object.keys(data));
      let pack = data[pack_id];

      if(pack === undefined || pack.white === undefined || pack.white.length === 0)
      {
        return reject(tries + 1);
      }
      else 
      {
        let card = rand_arr(pack.white);

        if(card.txt === undefined || card.pid === undefined || card.cid === undefined || 
          card.r !== 0 || picked.includes(card.txt))
        {
          return reject(tries + 1);
        }
        else
        {
          picked.push(card.txt);
          return resolve({...card, pack: pack.name});
        }
      }
    }).catch((tries) => {
      if(tries < 20){
        return pick_random_white_promise(tries);
      } else {
        console.error('pick failed', e);
        return false;
      }
    });
  };

  let share_id = '';
  const generate_card = (meaning, i, pick) =>
  {
    let classes = 'card';
    if(pick.r !== 0) classes += ' card_removed reason_' + pick.r;

    let card = '<div class="card_wrap"><div class="' + classes + '" id="card_' + i + '">'
              + '<div class="meaning">' + escapeHtml(meaning) + '</div>'
              + '<div class="text_wrap"><div class="text">' + escapeHtml(pick.txt) + '</div></div>'
              + '<div class="pack">' + escapeHtml(pick.pack) + '</div>'
              + '</div></div>';

    $('#spread').append(card);
  }

  const reasons = {
    1: 'Error: Removed for offensive language',
    2: 'Error: Duplicate card',
    3: 'Error: Card text too long',
    4: 'Error: Removed for grammatical errors', 
  };

  const get_cards = (id) =>
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
      let pack_id = parseInt(card_arr[0], 16);
      let card_id = parseInt(card_arr[1], 16);
      let pick = null;

      if(!data[pack_id])
      {
        console.error('invalid pack', pack_id);
        return false;
        break;
      }

      if(data[pack_id].r !== 0)
      {
        pick = {
          cid: card_id,
          pid: pack_id, 
          txt: reasons[data[pack_id].r] ? reasons[data[pack_id].r] : 'Error',
          r: data[pack_id].r
        };
      }
      else 
      {
        for(var j = 0; j < data[pack_id].white.length; j++)
        {
          if(data[pack_id].white[j].cid === card_id)
          {
            pick = data[pack_id].white[j];
            break;
          }
        }

        if(pick.r !== 0)
        {
          if(pick.r === 2){ //dupe card
            const split = pick.txt.split('-');
            const dup_pid = split[0] !== undefined ? +split[0] : false;
            const dup_cid = split[1] !== undefined ? +split[1] : false;
            let dup_found = false;

            if(data[dup_pid] && data[dup_pid].white)
            {
              if(data[dup_pid].r !== 0)
              {
                pick.r = data[dup_pid].r;
              }
              else 
              {
                for(var j = 0; j < data[dup_pid].white.length; j++)
                {
                  if(data[dup_pid].white[j].cid === dup_cid)
                  {
                    pick = data[dup_pid].white[j];
                    dup_found = true;
                    break;
                  }
                }
              }
            }

            if(dup_found === false) pick.txt = reasons[pick.r] ? reasons[pick.r] : 'Error';
          }
          
          if(pick.r !== 2 && pick.r !== 0)
          {
            pick.txt = reasons[pick.r] ? reasons[pick.r] : 'Error';
          }
        }
      }

      if(pick === null)
      {
        console.error('invalid card', card_id);
        return false;
        break;
      } else {
        pick.pack = data[pack_id].name;
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

    $('#spread_it').on('click', () =>
    {
      if(share_id) location.href = site_url;

      $('#spread').empty();
      picked = [];
      share_id = '';
      spread = $('#spread_select option:selected').val();

      Promise.all([
        pick_random_white_promise(0), 
        pick_random_white_promise(0), 
        pick_random_white_promise(0)
      ]).then((cards) => 
      {
        spreads[spread].forEach((meaning, i) => {
          share_id = share_id + (i > 0 ? 'g' : '') + cards[i].pid.toString(16) + 'h' + cards[i].cid.toString(16);
          generate_card(meaning, i, cards[i]);
        })
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
