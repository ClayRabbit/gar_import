@echo off
Z:\WS\modules\php\PHP_7.4\php Z:\WS\domains\gar\imp_a_check.php || goto :exit
Z:\WS\modules\php\PHP_7.4\php Z:\WS\domains\gar\imp_b_addr_obj.php || goto :exit
Z:\WS\modules\php\PHP_7.4\php Z:\WS\domains\gar\imp_c_index.php || goto :exit
Z:\WS\modules\php\PHP_7.4\php Z:\WS\domains\gar\imp_d_house.php || goto :exit
Z:\WS\modules\php\PHP_7.4\php Z:\WS\domains\gar\imp_e_index.php || goto :exit
Z:\WS\modules\php\PHP_7.4\php Z:\WS\domains\gar\imp_f_adm_hierarchy.php || goto :exit
Z:\WS\modules\php\PHP_7.4\php Z:\WS\domains\gar\imp_g_mun_hierarchy.php || goto :exit
Z:\WS\modules\php\PHP_7.4\php Z:\WS\domains\gar\imp_h_index.php || goto :exit
Z:\WS\modules\php\PHP_7.4\php Z:\WS\domains\gar\imp_i_touch.php || goto :exit
Z:\WS\modules\php\PHP_7.4\php Z:\WS\domains\gar\imp_j_house_fix.php || goto :exit
Z:\WS\modules\php\PHP_7.4\php Z:\WS\domains\gar\imp_k_addr_param.php || goto :exit
Z:\WS\modules\php\PHP_7.4\php Z:\WS\domains\gar\imp_l_house_param.php || goto :exit
Z:\WS\modules\php\PHP_7.4\php Z:\WS\domains\gar\imp_m_prerelease.php || goto :exit
Z:\WS\modules\php\PHP_7.4\php Z:\WS\domains\gar\imp_n_merge_addr.php || goto :exit
Z:\WS\modules\php\PHP_7.4\php Z:\WS\domains\gar\imp_o_merge_house.php || goto :exit
Z:\WS\modules\php\PHP_7.4\php Z:\WS\domains\gar\imp_p_index.php || goto :exit
Z:\WS\modules\php\PHP_7.4\php Z:\WS\domains\gar\imp_q_replacer.php || goto :exit

:exit
pause
