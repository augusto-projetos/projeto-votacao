// Adicionar CPF em um vetor e verifica-o
function adicionarCpf() {
    const inputNome = document.getElementById('nomeForm');
    const inputCPF = document.getElementById('cpf');
    const btnEnviar = document.getElementById('btn-form');
    const cpf = inputCPF.value.replace(/\D/g, '');
    const cpfArray = cpf.split('').map(Number);
    const repetidos = [
        '00000000000', '11111111111', '22222222222',
        '33333333333', '44444444444', '55555555555',
        '66666666666', '77777777777', '88888888888',
        '99999999999'
    ];

    // Se CPF incompleto, resetar visuais e desativar botão
    if (cpfArray.length < 11) {
        inputCPF.style.borderColor = '';
        btnEnviar.style.backgroundColor = '';
        btnEnviar.style.cursor = '';
        btnEnviar.disabled = true;
        btnEnviar.textContent = 'Enviar';
        return;
    }

    // VERIFICAÇÃO DOS CPFs REPETIDOS - Se for repetido, bloqueia e sai
    if (repetidos.includes(cpf)) {
        inputCPF.style.borderColor = 'red';
        btnEnviar.disabled = true;
        btnEnviar.textContent = 'Enviar';
        btnEnviar.style.cursor = 'default';
        btnEnviar.style.backgroundColor = 'red';
        return;
    }

    // Validação dos dígitos verificadores
    let teste1 = 0, teste2, verificador1, verificador2;

    for (let i = 0; i < 9; i++) {
        teste1 += cpfArray[i] * (10 - i);
    }

    if (teste1 % 11 < 2) {
        verificador1 = 0;
    } else {
        verificador1 = 11 - (teste1 % 11);
    }

    teste2 = cpfArray[0]*11 + cpfArray[1]*10 + cpfArray[2]*9 + cpfArray[3]*8 + cpfArray[4]*7 + cpfArray[5]*6 + cpfArray[6]*5 + cpfArray[7]*4 + cpfArray[8]*3 + verificador1*2;
    
    if (teste2 % 11 < 2) {
        verificador2 = 0;
    } else {
        verificador2 = 11 - (teste2 % 11);
    }

    if (verificador1 === cpfArray[9] && verificador2 === cpfArray[10]) {
        // CPF válido
        inputCPF.style.borderColor = 'green';

        if (inputNome.value.trim() !== '') {
            // Verifica via AJAX se CPF já está no banco
            const formData = new FormData();
            formData.append('cpf', inputCPF.value);

            fetch('php/verificar_cpf.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                if (data.trim() === 'existe') {
                    btnEnviar.textContent = 'Conferir';
                } else {
                    btnEnviar.textContent = 'Enviar';
                }

                btnEnviar.disabled = false;
                btnEnviar.style.backgroundColor = 'green';
                btnEnviar.style.cursor = 'pointer';
            });
        } else {
            btnEnviar.disabled = true;
            btnEnviar.textContent = 'Enviar';
            btnEnviar.style.backgroundColor = '';
            btnEnviar.style.cursor = 'pointer';
        }
    } else {
        // CPF inválido - mas com 11 números, então marcar vermelho e desabilitar botão
        inputCPF.style.borderColor = 'red';
        btnEnviar.disabled = true;
        btnEnviar.textContent = 'Enviar';
        btnEnviar.style.cursor = 'default';
        btnEnviar.style.backgroundColor = 'red';
    }

    inputNome.addEventListener('input', adicionarCpf);
};

// Formata o input para formato CPF
const cpfInput = document.getElementById('cpf');

    cpfInput.addEventListener('input', () => {
        let value = cpfInput.value.replace(/\D/g, '').slice(0, 11);

        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');

        cpfInput.value = value;

        adicionarCpf();
    }
);

// Verificação de erros
document.getElementById('formulario').addEventListener('submit', function (e) {
    const nomeInput = document.getElementById('nomeForm');
    const nomeOriginal = nomeInput.value;
    const nome = nomeOriginal.trim();

    // Verifica se tem só espaços ou está vazio
    if (nome.length === 0) {
        e.preventDefault();
        alert("O campo nome não pode estar vazio.");
        nomeInput.focus();
        return;
    }

    // Verifica se ultrapassa 30 caracteres (sem contar espaços extras no início/fim)
    if (nomeOriginal.length > 30) {
        e.preventDefault();
        alert("O nome não pode ter mais que 50 caracteres.");
        nomeInput.focus();
        return;
    }

    // Verifica se o nome contém apenas letras e espaços
    const nomeValido = /^[A-Za-zÀ-ÿ\s]+$/.test(nome);

    if (!nomeValido) {
        e.preventDefault();
        alert("Por favor, digite um nome válido (apenas letras e espaços, sem números ou símbolos).");
        nomeInput.focus();
        return;
    }
});
