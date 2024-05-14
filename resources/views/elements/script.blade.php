<script>
    const votes = @json($votes);
    const max = 5;
    let loading = true;

    window.addEventListener("load", () => {
        displayVotes(1);
    });


    function displayVotes(currentPage) {
        const tableVoting = document.getElementById('voting');

        tableVoting.innerHTML = ''; // Effacer le contenu de la table
        let row = ''; // Initialiser la chaîne HTML

        const startIndex = (currentPage - 1) * max;
        const endIndex = startIndex + max;

        // Boucle sur les clés d'ojbect et affichage
        Object.keys(votes).slice(startIndex, endIndex).forEach((key, idx) => {
            const vote = votes[key];
            row += `
            <tr>
                <th scope="row">#${startIndex + idx + 1}</th>
                <td>${vote.user.name}</td>
                <td>${vote.votes}</td>
            </tr>
        `;
        });

        tableVoting.innerHTML += row; // Ajouter la chaîne HTML à la table

        generatePagination(currentPage, Object.keys(votes).length, max);
    }


    function generatePagination(currentPage, nbTotalItem, limit){
        const paginationWrapper = document.querySelector('.pagination-wrapper');
        paginationWrapper.innerHTML = '';

        if(Object.keys(votes).length > max) {
            paginationWrapper.classList.add('pt-5', 'pb-3');

            const retourButton = document.createElement('li');
            // Creation du bouton "Retour"
            retourButton.classList.add('page-item');
            retourButton.innerHTML = `
                <a href="#" class="page-link bg-transparent border-0">
                    <i class="bi bi-arrow-left"></i>
                </a>
            `;
            retourButton.addEventListener('click', () => {
                if (currentPage > 1) displayVotes(currentPage - 1);
            })
            paginationWrapper.prepend(retourButton);

            //Generation des pages
            pagination(currentPage, Math.ceil(nbTotalItem / limit)).map(page => {
                console.log(page)
                const pageButton = document.createElement('li');

                if (page === '...') {
                    pageButton.innerHTML = `
                        <span>${page}</span>
                    `;
                } else {
                    pageButton.classList.add('page-item');
                    pageButton.innerHTML = `
                    <a href="#" class="page-link bg-transparent border-0 p-0">
                        ${page}
                    </a>`;
                    if (page === currentPage) pageButton.classList.add('disabled', 'active');
                    pageButton.addEventListener('click', () => {
                        displayVotes(page);
                    })
                }
                paginationWrapper.appendChild(pageButton);
            })

            // Creation du bouton "Suivant"
            const suivantButton = document.createElement('li');
            suivantButton.classList.add('page-item');
            suivantButton.innerHTML = `
                <a href="#" class="page-link bg-transparent border-0">
                    <i class="bi bi-arrow-right"></i>
                </a>
            `;
            suivantButton.addEventListener('click', () => {
                if (currentPage < Math.ceil(nbTotalItem / limit)) displayVotes(currentPage + 1);
            })
            paginationWrapper.appendChild(suivantButton);
        }
    }


    function pagination(currentPage, maxPages){
        const pagination = [];
        // Seuil max de pagination
        const paginationThreshold = 6;

        if(maxPages <= paginationThreshold){
            for(let i = 1; i <= maxPages; i++){
                pagination.push(i);
            }
        } else {
            if (currentPage <= paginationThreshold - 2){
                for(let i = 1; i < paginationThreshold ; i++){
                    pagination.push(i);
                }
                pagination.push('...');
                pagination.push(maxPages);
            } else if (currentPage >= maxPages - (paginationThreshold - 3)){
                pagination.push(1);
                pagination.push('...');
                for(let i = maxPages - (paginationThreshold - 2); i <= maxPages; i++){
                    pagination.push(i);
                }
            } else {
                pagination.push(1);
                pagination.push('...');
                for (let i = currentPage - 1; i <= currentPage + 1; i++){
                    pagination.push(i);
                }
                pagination.push('...');
                pagination.push(maxPages);
            }
        }

        return pagination;
    }
</script>
