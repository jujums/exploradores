async function verificar() {
  let pontos = 0;
  const respostas = {
    q1: "Leão",
    q2: "Leonardo", 
    q3: "Um bom aluno"
  };

  for (const q in respostas) {
    const marcada = document.querySelector(`input[name="${q}"]:checked`);
    const textoMarcado = marcada ? marcada.value : null;
    const acertou = textoMarcado === respostas[q];
    const questionCard = document.querySelector(`input[name="${q}"]`).closest(".question-card");

    if (acertou) pontos++;
    questionCard.classList.toggle("correct", acertou);
    questionCard.classList.toggle("incorrect", !acertou);

    await salvarJogada({
      jogo_slug:      'historia',
      fase:           q,
      movimento_tipo: 'responder_pergunta',
      movimento:      { questao: q, marcada: textoMarcado, esperada: respostas[q] },
      correta:        acertou,
    });
  }

  const total = Object.keys(respostas).length;
  const resultado = document.getElementById("resultado");
  resultado.textContent = `Você acertou ${pontos} de ${total} questões! 🦁📚`;
  resultado.classList.add("show");
}