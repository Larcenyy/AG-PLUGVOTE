<script>
    window.addEventListener("load", () => {
        const tableVoting = document.getElementById('voting');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');

        const itemsPerPage = 5;
        let currentPage = 0;

        const votes = @json($votes);

        console.log(votes)
        console.log(votes.length)

        function displayVotes() {
            tableVoting.innerHTML = ''; // Effacer le contenu de la table
            const startIndex = currentPage * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;

            let row = ''; // Initialiser la chaîne HTML
            for (let i = startIndex; i < endIndex && i < votes.length; i++) {
                const vote = votes[i];
                row += `
                    <tr>
                        <th scope="row">#${i + 1}</th>
                        <td>${vote.user.name}</td>
                        <td>${vote.votes}</td>
                    </tr>
                `;
            }
            tableVoting.innerHTML += row; // Ajouter la chaîne HTML à la table
        }

        function goToPrevPage() {
            if (currentPage > 0) {
                currentPage--;
                displayVotes();
            }
        }

        function goToNextPage() {
            const totalPages = Math.ceil(votes.length / itemsPerPage);
            if (currentPage < totalPages - 1) {
                currentPage++;
                displayVotes();
            }
        }

        displayVotes();

        prevBtn.addEventListener('click', goToPrevPage);
        nextBtn.addEventListener('click', goToNextPage);
    });
</script>
