<?php

use Pecee\SimpleRouter\SimpleRouter as Router;

Router::setDefaultNamespace('App\Controller');

// ==========================================
// ROTAS DE ERROS
// ==========================================
Router::get('/404', 'ErroController@erro404');

// ==========================================
// ROTAS DA DASHBOARD
// ==========================================
Router::get('/', 'DashboardController@mostrar');
Router::get('dashboard/', 'DashboardController@mostrar');

// ==========================================
// ROTAS DE PSICOLOGOS
// ==========================================
Router::get('psicologos/listar', 'PsicologoController@listar');
Router::get('psicologos/excluir/{id}', 'PsicologoController@excluir');

// Cadastro
Router::get('psicologos/cadastrar', 'PsicologoController@exibirFormularioCadastro');
Router::post('psicologos/cadastrar', 'PsicologoController@processarCadastro');

// Edição
Router::get('psicologos/editar/{id}', 'PsicologoController@exibirFormularioEdicao');
Router::post('psicologos/editar/{id}', 'PsicologoController@processarEdicao');


// ==========================================
// ROTAS DE PACIENTES
// ==========================================
Router::get('pacientes/listar/', 'PacienteController@listar');
Router::get('pacientes/excluir/{id}', 'PacienteController@excluir');

// Cadastro
Router::get('pacientes/cadastrar', 'PacienteController@exibirFormularioCadastro');
Router::post('pacientes/cadastrar', 'PacienteController@processarCadastro');

// Edição
Router::get('pacientes/editar/{id}', 'PacienteController@exibirFormularioEdicao');
Router::post('pacientes/editar/{id}', 'PacienteController@processarEdicao');

// ==========================================
// ROTAS DE PRODUTOS
// ==========================================
Router::get('produtos/listar/', 'ProdutoController@listar');
Router::get('produtos/excluir/{id}', 'ProdutoController@excluir');

// Cadastro
Router::get('produtos/cadastrar', 'ProdutoController@exibirFormularioCadastro');
Router::post('produtos/cadastrar', 'ProdutoController@processarCadastro');

// Edição
Router::get('produtos/editar/{id}', 'ProdutoController@exibirFormularioEdicao');
Router::post('produtos/editar/{id}', 'ProdutoController@processarEdicao');

// ==========================================
// ROTAS DE VENDAS
// ==========================================
Router::get('vendas/listar/', 'VendaController@listar');

// Cadastro
Router::get('vendas/cadastrar', 'VendaController@exibirFormularioCadastro');
Router::post('vendas/cadastrar', 'VendaController@processarCadastro');

// Edição
Router::get('vendas/editar/{id}', 'VendaController@exibirFormularioEdicao');
Router::post('vendas/editar/{id}', 'VendaController@processarEdicao');

// ==========================================
// ROTAS DE SESSÕES
// ==========================================
Router::get('sessoes/listar/', 'SessaoController@listar');

// Cadastro
Router::get('sessoes/cadastrar', 'SessaoController@exibirFormularioCadastro');
Router::post('sessoes/cadastrar', 'SessaoController@processarCadastro');

// Edição
Router::get('sessoes/editar/{id}', 'SessaoController@exibirFormularioEdicao');
Router::post('sessoes/editar/{id}', 'SessaoController@processarEdicao');
Router::post('sessoes/sessaoRealizada/{id}', 'SessaoController@processarSessaoRealizada');
Router::post('sessoes/cancelar/{id}', 'SessaoController@processarCancelamento');

// ==========================================
// ROTAS DE REPASSES
// ==========================================
Router::get('repasses/listar/', 'RepasseController@listar');

// ==========================================
// ROTAS DE DESPESAS
// ==========================================
Router::get('despesas/listar/', 'DespesaController@listar');

// Cadastro
Router::get('despesas/cadastrar', 'DespesaController@exibirFormularioCadastro');
Router::post('despesas/cadastrar', 'DespesaController@processarCadastro');

// Edição
Router::get('despesas/editar/{id}', 'DespesaController@exibirFormularioEdicao');
Router::post('despesas/editar/{id}', 'DespesaController@processarEdicao');

// ==========================================
// ROTAS DE FLUXO DE CAIXA
// ==========================================
Router::get('fluxocaixa/listar/', 'FluxoCaixaController@listar');
