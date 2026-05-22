<?php

function tabela_sessoes_proximas($sessoes_proximas) : string {
    // string acumuladora
    $html = '';

    if (empty($sessoes_proximas)) {
        $html .= '
        <tr>
            <td colspan="5" class="w3-center">Nenhuma sessão agendada.</td>
        </tr>';
    } else {
        foreach ($sessoes_proximas as $s) {
            $data       = date('d/m/Y', strtotime($s['data_agendada']));
            $hora       = htmlspecialchars($s['hora_agendada']);
            $paciente   = htmlspecialchars($s['paciente']);
            $psicologo  = htmlspecialchars($s['psicologo']);
            $status     = htmlspecialchars($s['status']);

            $html .= "
            <tr>
                <td>$data</td>
                <td>$hora</td>
                <td>$paciente</td>
                <td>$psicologo</td>
                <td>$status</td>
            </tr>";
        }
    }

    return $html;
}

function tabela_renovacoes($renovacoes) {
    $html = '';

    if (empty($renovacoes)) {
        $html .= '
        <tr>
            <td colspan="6" class="w3-center">Nenhum paciente precisa renovar agora.</td>
        </tr>';
    } else {
        foreach ($renovacoes as $r) {
            $paciente           = htmlspecialchars($r['paciente']);
            $psicologo          = htmlspecialchars($r['psicologo']);
            $produto            = htmlspecialchars($r['produto']);
            $sessoes_realizadas = (int)$r['sessoes_realizadas'];
            $sessoes_restantes  = (int)$r['sessoes_restantes'];
            $ultima_sessao      = $r['ultima_sessao'] ? date('d/m/Y', strtotime($r['ultima_sessao'])) : '-';

            $html .= "
            <tr>
                <td>$paciente</td>
                <td>$psicologo</td>
                <td>$produto</td>
                <td>$sessoes_realizadas</td>
                <td>$sessoes_restantes</td>
                <td>$ultima_sessao</td>
            </tr>";
        }
    }

    return $html;
}

function tabela_sessoes_psicologos($sessoes_psicologo) : string {
    $html = '';

    if (empty($sessoes_psicologo)) {
        $html .= '
        <tr>
            <td colspan="2" class="w3-center">Nenhuma sessão registrada este mês.</td>
        </tr>';
    } else {
        foreach ($sessoes_psicologo as $p) {
            $psicologo = htmlspecialchars($p['psicologo']);
            $total     = (int)$p['total'];

            $html = "
            <tr>
                <td>$psicologo</td>
                <td>$total</td>
            </tr>";
        }
    }

    return $html;
}