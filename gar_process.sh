#!/bin/sh
php imp_a_check.php || exit $?
php imp_b_addr_obj.php || exit $?
php imp_c_index.php || exit $?
php imp_d_house.php || exit $?
php imp_e_index.php || exit $?
php imp_f_adm_hierarchy.php || exit $?
php imp_g_mun_hierarchy.php || exit $?
php imp_h_index.php || exit $?
php imp_i_touch.php || exit $?
php imp_j_house_fix.php || exit $?
php imp_k_addr_param.php || exit $?
php imp_l_house_param.php || exit $?
php imp_m_prerelease.php || exit $?
php imp_n_merge_addr.php || exit $?
php imp_o_merge_house.php || exit $?
php imp_p_index.php || exit $?
php imp_q_replacer.php || exit $?

