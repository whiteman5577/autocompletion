<?php
    
    $data = unserialize(file_get_contents('towns.txt')); // Récupération de la liste complète des villes
    $dataLen = count($data);
    
    sort($data); // On trie les villes dans l'ordre alphabétique
    
    $results = array(); // Le tableau où seront stockés les résultats de la recherche
    
    // La boucle ci-dessous parcourt tout le tableau $data, jusqu'à un maximum de 10 résultats
    
    
    for ($i = 0 ; $i < $dataLen && count($results) < 10 ; $i++) {
        if (stripos($data[$i], $_GET['s']) === 0) { // Si la valeur commence par les mêmes caractères que la recherche
    
            array_push($results, $data[$i]); // On ajoute alors le résultat à la liste à retourner
    
        }
    }
    
    echo implode('|', $results); // Et on affiche les résultats séparés par une barre verticale |
    
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8" />
    <title>TP : Un système d'auto-complétion</title>

    <style>
      body {
        margin: auto;
        padding-top: 80px;
        width: 300px;
        font-family: sans-serif;
        font-size: 0.8em;
        background-color: #F5F5F5;
      }

      input[type="submit"] {
        margin-left: 10px;
        width: 50px; height: 26px;
      }

      #search {
        padding: 2px 4px;
        width: 220px; height: 22px;
        border: 1px solid #AAA;
      }

      #search:hover, #search:focus {
        border-color: #777;
      }

      #results {
        display: none;
        width: 228px;
        border: 1px solid #AAA;
        border-top-width: 0;
        background-color: #FFF;
      }

      #results div {
        width: 220px;
        padding: 2px 4px;
        text-align: left;
        border: 0;
        background-color: #FFF;
      }

      #results div:hover, .result_focus {
        background-color: #DDD !important;
      }
    </style>
  </head>

  <body>

    <input id="search" type="text" autocomplete="off" />

    <div id="results"></div>

    <script>

    (function() {

        var searchElement = document.getElementById('search'),
            results = document.getElementById('results'),
            selectedResult = -1, // Permet de savoir quel résultat est sélectionné : -1 signifie "aucune sélection"
            previousRequest, // On stocke notre précédente requête dans cette variable
            previousValue = searchElement.value; // On fait de même avec la précédente valeur



        function getResults(keywords) { // Effectue une requête et récupère les résultats
          
            var xhr = new XMLHttpRequest();
            xhr.open('GET', './search.php?s='+ encodeURIComponent(keywords));

            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    
                    displayResults(xhr.responseText);

                }
            };

            xhr.send(null);

            return xhr;

        }

        function displayResults(response) { // Affiche les résultats d'une requête
          
            results.style.display = response.length ? 'block' : 'none'; // On cache le conteneur si on n'a pas de résultats

            if (response.length) { // On ne modifie les résultats que si on en a obtenu

                response = response.split('|');
                var responseLen = response.length;

                results.innerHTML = ''; // On vide les résultats

                for (var i = 0, div ; i < responseLen ; i++) {

                    div = results.appendChild(document.createElement('div'));
                    div.innerHTML = response[i];
                    
                    div.onclick = function() {
                        chooseResult(this);
                    };

                }

            }

        }

        function chooseResult(result) { // Choisi un des résultats d'une requête et gère tout ce qui y est attaché
          
            searchElement.value = previousValue = result.innerHTML; // On change le contenu du champ de recherche et on enregistre en tant que précédente valeur
            results.style.display = 'none'; // On cache les résultats
            result.className = ''; // On supprime l'effet de focus
            selectedResult = -1; // On remet la sélection à "zéro"
            searchElement.focus(); // Si le résultat a été choisi par le biais d'un clique alors le focus est perdu, donc on le réattribue

        }



        searchElement.onkeyup = function(e) {
          
            e = e || window.event; // On n'oublie pas la compatibilité pour IE

            var divs = results.getElementsByTagName('div');

            if (e.keyCode == 38 && selectedResult > -1) { // Si la touche pressée est la flèche "haut"
              
                divs[selectedResult--].className = '';
                
                if (selectedResult > -1) { // Cette condition évite une modification de childNodes[-1], qui n'existe pas, bien entendu
                    divs[selectedResult].className = 'result_focus';
                }

            }

            else if (e.keyCode == 40 && selectedResult < divs.length - 1) { // Si la touche pressée est la flèche "bas"
              
                results.style.display = 'block'; // On affiche les résultats

                if (selectedResult > -1) { // Cette condition évite une modification de childNodes[-1], qui n'existe pas, bien entendu
                    divs[selectedResult].className = '';
                }

                divs[++selectedResult].className = 'result_focus';

            }

            else if (e.keyCode == 13 && selectedResult > -1) { // Si la touche pressée est "Entrée"
              
                chooseResult(divs[selectedResult]);

            }

            else if (searchElement.value != previousValue) { // Si le contenu du champ de recherche a changé

                previousValue = searchElement.value;

                if (previousRequest && previousRequest.readyState < 4) {
                    previousRequest.abort(); // Si on a toujours une requête en cours, on l'arrête
                }

                previousRequest = getResults(previousValue); // On stocke la nouvelle requête

                selectedResult = -1; // On remet la sélection à "zéro" à chaque caractère écrit

            }

        };

    })();

    </script>

  </body>
</html>