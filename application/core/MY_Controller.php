<?php

Class MY_Controller extends CI_Controller
{
    //bien gui du lieu sang ben view
    public $data = array();

    function __construct()
    {
        //ke thua tu CI_Controller
        parent::__construct();
        header('Access-Control-Allow-Origin: *');
        $controller = $this->uri->segment(1);
        switch ($controller) {
            case 'admin' : {
                $this->load->helper('language');
                $this->load->model('user_model');
                $this->lang->load('admin/common');
                $admin_login = $this->session->userdata('user_adminnew_login');
                $this->data['login'] = $admin_login;
                if ($admin_login) {
                    $admin_info = $this->user_model->get_info($admin_login);
                    $this->data['admin_info'] = $admin_info;
                }

                $this->load->helper('admin');
                $this->_check_login();
                break;
            }
            default: {

            $linkapi = $this->config->item("api_url");
            $this->data['linkapi'] = $linkapi;
            $userinfo = $this->session->userdata('infouser');
            $this->data['userinfo'] = $userinfo;
            $this->load->helper('news_helper');
            $this->load->model('category_model');
            $this->load->model('adv_model');
            $this->load->model('system_model');
            $list = $this->get_list_category();
            $this->data['menu_list'] = $list;
            $listfooter = $this->get_list_category_footer();
            $this->data['listfooter'] = $listfooter;
            //quảng cáo left
            $list1 = $this->adv_model->get_adv_left();
            $this->data['list1'] = $list1;
            $list2 = $this->adv_model->get_adv_right();
            $this->data['list2'] = $list2;
            $list3 = $this->system_model->get_list();
            $this->data['list3'] = $list3;
            
            foreach($list3 as $item)
            {
                $this->data['linkface'] = $item->linkface;
                $this->data['linkgoogle'] = $item->linkgoogle;
                $this->data['linktwiter'] = $item->linktwiter;
                $this->data['linkyoutube'] = $item->linkyoutube;
                $this->data['linkblog'] = $item->linkblog;
                $this->data['linklogin'] = $item->linklogin;
                $this->data['images'] = $item->images;
                $this->data['contact'] = $item->contact;
                $this->data['codeGA'] = $item->codeGA;
                $this->data['h1'] = $item->h1;
				$this->data['sign'] = $item->sign;
                $this->data['popup'] = $item->ispopup;
                $this->data['linkpopup'] = $item->linkpopup;
                $this->data['TitleLevelVip'] = $item->TitleLevelVip;
                $this->data['ContentLevelVip'] = $item->ContentLevelVip;
                $this->data['Images1LevelVip'] = $item->Images1LevelVip;
                $this->data['Images2LevelVip'] = $item->Images2LevelVip;
                $this->data['TitleBenefit'] = $item->TitleBenefit;
                $this->data['ContentBenefit'] = $item->ContentBenefit;
                $this->data['Images1Benefit'] = $item->Images1Benefit;
                $this->data['Images2Benefit'] = $item->Images2Benefit;
                $this->data['Images3Benefit'] = $item->Images3Benefit;
                $this->data['Images4Benefit'] = $item->Images4Benefit;
                $this->data['TextImages1Benefit'] = $item->TextImages1Benefit;
                $this->data['TextImages2Benefit'] = $item->TextImages2Benefit;
                $this->data['TextImages3Benefit'] = $item->TextImages3Benefit;
                $this->data['TextImages4Benefit'] = $item->TextImages4Benefit;

            }
            break;
            }

        }
    }

    /*
     * Kiem tra trang thai dang nhap cua admin
     */
    private function _check_login()
    {
        $controller = $this->uri->rsegment('1');
        $controller = strtolower($controller);

        $login = $this->session->userdata('user_adminnew_login');
        //neu ma chua dang nhap,ma truy cap 1 controller khac login
        if (!$login && $controller != 'login') {
            redirect(admin_url('login'));
        }
        //neu ma admin da dang nhap thi khong cho phep vao trang login nua.
        if ($login && $controller == 'login') {
            redirect(admin_url('home'));
        }
    }



    function Check_Url_Admin($current_url)
    {
        $this->load->model('accesslink_model');
        //lấy id của user đăng nhập
        $admin_login = $this->session->userdata('user_id_login');
        //lấy tất cả các link của user đó
        $list_link = $this->accesslink_model->get_list_linkacess_userid($admin_login);

        //lấy url hiện tại
        $stack = array();
        foreach ($list_link as $item) {
            array_push($stack, $item->Link);
        }
        if (in_array($current_url, $stack)) {
            return true;
        } else {
            return false;
        }
    }

    function create_slug($str)
    {
        $str = trim(mb_strtolower($str));
        $str = preg_replace('/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/', 'a', $str);
        $str = preg_replace('/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/', 'e', $str);
        $str = preg_replace('/(ì|í|ị|ỉ|ĩ)/', 'i', $str);
        $str = preg_replace('/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/', 'o', $str);
        $str = preg_replace('/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/', 'u', $str);
        $str = preg_replace('/(ỳ|ý|ỵ|ỷ|ỹ)/', 'y', $str);
        $str = preg_replace('/(đ)/', 'd', $str);
        $str = preg_replace('/[^a-z0-9-\s]/', '', $str);
        $str = preg_replace('/([\s]+)/', '-', $str);
        return $str;
    }

    public function init_pagination($base_url, $total_rows, $per_page = 100, $segment)
    {
        $ci =& get_instance();
        $config['base_url'] = $base_url;
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config["uri_segment"] = $segment;
        $config['next_link'] = 'Trang kế tiếp';
        $config['prev_link'] = 'Trang trước';
        $ci->pagination->initialize($config);
        return $config;
    }

    function dateDiff($start, $end)
    {
        date_default_timezone_set('Asia/Bangkok');
        $start_ts = strtotime($start);
        $end_ts = strtotime($end);
        $diff = $end_ts - $start_ts;
        return round($diff / 86400);
    }


    function rand_string($length)
    {
        $str = "";
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $size = strlen($chars);
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[rand(0, $size - 1)];
        }
        return $str;
    }

    function get_list_category()
    {
        $str = "";
        $categorys = $this->category_model->get_category();
       
        if (!empty($categorys)) {
                    $str .= "<li>";
                    $str .= "   <a href=" . base_url('/') . ">Trang chủ</a>";
                    $str .= "</li>";
            foreach ($categorys as $category) {
                if ($category->typepage == 1) {
                    $str .= "<li>";
                    $str .= " <a href=" . base_url('vinh-danh') . ">" . $category->catname . "</a>";
                    $str .= "<ul>";
                    $str .= $this->get_sub_list_category($category->id, $i = 0);
                    $str .= "</ul>";
                    $str .= "</li>";
                } else if ($category->typepage == 2) {
                    $str .= "<li>";
                    $str .= " <a href=" . base_url('dac-quyen-vip') . ">" . $category->catname . "</a>";
                    $str .= "<ul>";
                    $str .= $this->get_sub_list_category($category->id, $i = 0);
                    $str .= "</ul>";
                    $str .= "</li>";
                } else if ($category->typepage == 3) {
                    $str .= "<li>";
                    $str .= " <a href=" . base_url('lien-he') . ">" . $category->catname . "</a>";
                    $str .= "<ul>";
                    $str .= $this->get_sub_list_category($category->id, $i = 0);
                    $str .= "</ul>";
                    $str .= "</li>";
                } else if ($category->typepage == 4) {
                    $str .= "<li>";
                    $str .= " <a href=" . base_url('hoi-dap') . ">" . $category->catname . "</a>";
                    $str .= "<ul>";
                    $str .= $this->get_sub_list_category($category->id, $i = 0);
                    $str .= "</ul>";
                    $str .= "</li>";
                } else if ($category->typepage == 5) {
                    $str .= "<li>";
                    $str .= " <a href=" . base_url('cap-do-vip') . " id='profilevip'>" . $category->catname . "</a>";
                    $str .= "<ul>";
                    $str .= $this->get_sub_list_category($category->id, $i = 0);
                    $str .= "</ul>";
                    $str .= "</li>";
                } else if ($category->typepage == 6) {
                    $str .= "<li id='profilevip'>";
                    $str .= " <a href='#' >" . $category->catname . "</a>";
                    $str .= "<ul>";
                    $str .= $this->get_sub_list_category($category->id, $i = 0);
                    $str .= "</ul>";
                    $str .= "</li>";
                } else if ($category->typepage == 0) {
                    $str .= "<li>";
                    $str .= " <a href=" . base_url('danh-muc/' . $category->seolink . '-' . $category->id) . ">" . $category->catname . "</a>";
                    $str .= "<ul>";
                    $str .= $this->get_sub_list_category($category->id, $i = 0);
                    $str .= "</ul>";
                    $str .= "</li>";
                }

            }
        }
        return $str;
    }

    function get_sub_list_category($category_ids, $i = 0)
    {
        $str = "";
        $sub_categorys = $this->category_model->get_subcategory($category_ids);
        //kiem tra get subcategory co ton ai hay
        if (!empty($sub_categorys)) {
            foreach ($sub_categorys as $sub_category) {
                if ($sub_category->typepage == 1) {
                    $str .= "<li>";
                    $str .= " <a href=" . base_url('vinh-danh') . ">" . $sub_category->catname . "</a>";
                    $str .= "<ul>";
                } else if ($sub_category->typepage == 2) {
                    $str .= "<li>";
                    $str .= " <a href=" . base_url('dac-quyen-vip') . ">" . $sub_category->catname . "</a>";
                    $str .= "<ul>";
                } else if ($sub_category->typepage == 3) {
                    $str .= "<li>";
                    $str .= " <a href=" . base_url('lien-he') . ">" . $sub_category->catname . "</a>";
                    $str .= "<ul>";
                } else if ($sub_category->typepage == 4) {
                    $str .= "<li>";
                    $str .= " <a href=" . base_url('hoi-dap') . ">" . $sub_category->catname . "</a>";
                    $str .= "<ul>";
                } else if ($sub_category->typepage == 5) {
                    $str .= "<li>";
                    $str .= " <a href=" . base_url('cap-do-vip') . ">" . $sub_category->catname . "</a>";
                    $str .= "<ul>";
                } else if ($sub_category->typepage == 6) {
                    $str .= "<li  id='profilevip'>";
                    $str .= " <a href='#'>" . $sub_category->catname . "</a>";
                    $str .= "<ul>";
                } else if ($sub_category->typepage == 0) {
                    $str .= "<li>";
                    $str .= " <a href=" . base_url('danh-muc/' . $sub_category->seolink . '-' . $sub_category->id) . ">" . $sub_category->catname . "</a>";
                    $str .= "<ul>";
                }
                $sub_category_con = $this->category_model->get_subcategory($sub_category->id);
                if (!empty($sub_category_con)) {
                    foreach ($sub_category_con as $sub_category) {
                        if ($sub_category->typepage == 1) {
                            $str .= "<li>";
                            $str .= " <a href=" . base_url('vinh-danh') . ">" . $sub_category->catname . "</a>";
                            $str .= "<li>";
                        } else if ($sub_category->typepage == 2) {
                            $str .= "<li>";
                            $str .= " <a href=" . base_url('dac-quyen-vip') . ">" . $sub_category->catname . "</a>";
                            $str .= "<li>";
                        } else if ($sub_category->typepage == 3) {
                            $str .= "<li>";
                            $str .= " <a href=" . base_url('lien-he') . ">" . $sub_category->catname . "</a>";
                            $str .= "<li>";
                        } else if ($sub_category->typepage == 4) {
                            $str .= "<li>";
                            $str .= " <a href=" . base_url('hoi-dap') . ">" . $sub_category->catname . "</a>";
                            $str .= "<li>";
                        } else if ($sub_category->typepage == 5) {
                            $str .= "<li>";
                            $str .= " <a href=" . base_url('cap-do-vip') . ">" . $sub_category->catname . "</a>";
                            $str .= "<li>";
                        } else if ($sub_category->typepage == 6) {
                            $str .= "<li id='profilevip'>";
                            $str .= " <a href='#' >" . $sub_category->catname . "</a>";
                            $str .= "<li>";
                        } else if ($sub_category->typepage == 0) {
                            $str .= "<li>";
                            $str .= " <a href=" . base_url('danh-muc/' . $sub_category->seolink . '-' . $sub_category->id) . ">" . $sub_category->catname . "</a>";
                            $str .= "</li>";
                        }
                    }
                }
                $str .= "</ul>";
                $str .= "</li>";


                if ($sub_category->id) {
                    $str .= $this->get_sub_list_category($sub_category->id, $i++);
                }
            }

        }
        return $str;
    }

    function get_sub_list_category_faq($category_ids, $i = 0)
    {
        $str = "";
        $sub_categorys = $this->category_model->get_subcategory_faq($category_ids);
        //kiem tra get subcategory co ton ai hay
        if (!empty($sub_categorys)) {
            foreach ($sub_categorys as $sub_category) {
                if ($sub_category->typepage == 1) {
                    $str .= "<li>";
                    $str .= " <a href=" . base_url('vinh-danh') . ">" . $sub_category->catname . "</a>";
                    $str .= "<ul>";
                } else if ($sub_category->typepage == 2) {
                    $str .= "<li>";
                    $str .= " <a href=" . base_url('dac-quyen-vip') . ">" . $sub_category->catname . "</a>";
                    $str .= "<ul>";
                } else if ($sub_category->typepage == 3) {
                    $str .= "<li>";
                    $str .= " <a href=" . base_url('lien-he') . ">" . $sub_category->catname . "</a>";
                    $str .= "<ul>";
                } else if ($sub_category->typepage == 4) {
                    $str .= "<li>";
                    $str .= " <a href=" . base_url('hoi-dap') . ">" . $sub_category->catname . "</a>";
                    $str .= "<ul>";
                } else if ($sub_category->typepage == 5) {
                    $str .= "<li>";
                    $str .= " <a href=" . base_url('cap-do-vip') . ">" . $sub_category->catname . "</a>";
                    $str .= "<ul>";
                } else if ($sub_category->typepage == 6) {
                    $str .= "<li>";
                    $str .= " <a href='#' id='profilevip'>" . $sub_category->catname . "</a>";
                    $str .= "<ul>";
                } else if ($sub_category->typepage == 0) {
                    $str .= "<li>";
                    $str .= " <a href=" . base_url('danh-muc/' . $sub_category->seolink . '-' . $sub_category->id) . ">" . $sub_category->catname . "</a>";
                    $str .= "<ul>";
                }
                $sub_category_con = $this->category_model->get_subcategory_faq($sub_category->id);
                if (!empty($sub_category_con)) {
                    foreach ($sub_category_con as $sub_category) {
                        if ($sub_category->typepage == 1) {
                            $str .= "<li>";
                            $str .= " <a href=" . base_url('vinh-danh') . ">" . $sub_category->catname . "</a>";
                            $str .= "<li>";
                        } else if ($sub_category->typepage == 2) {
                            $str .= "<li>";
                            $str .= " <a href=" . base_url('dac-quyen-vip') . ">" . $sub_category->catname . "</a>";
                            $str .= "<li>";
                        } else if ($sub_category->typepage == 3) {
                            $str .= "<li>";
                            $str .= " <a href=" . base_url('lien-he') . ">" . $sub_category->catname . "</a>";
                            $str .= "<li>";
                        } else if ($sub_category->typepage == 4) {
                            $str .= "<li>";
                            $str .= " <a href=" . base_url('hoi-dap') . ">" . $sub_category->catname . "</a>";
                            $str .= "<li>";
                        } else if ($sub_category->typepage == 5) {
                            $str .= "<li>";
                            $str .= " <a href=" . base_url('cap-do-vip') . ">" . $sub_category->catname . "</a>";
                            $str .= "<li>";
                        } else if ($sub_category->typepage == 6) {
                            $str .= "<li id='profilevip'>";
                            $str .= " <a href='#'>" . $sub_category->catname . "</a>";
                            $str .= "<li>";
                        } else if ($sub_category->typepage == 0) {
                            $str .= "<li>";
                            $str .= " <a href=" . base_url('danh-muc/' . $sub_category->seolink . '-' . $sub_category->id) . ">" . $sub_category->catname . "</a>";
                            $str .= "</li>";
                        }
                    }
                }
                $str .= "</ul>";
                $str .= "</li>";


                if ($sub_category->id) {
                    $str .= $this->get_sub_list_category_faq($sub_category->id, $i++);
                }
            }

        }
        return $str;
    }

    function get_list_category_footer()
    {
        $str = "";
        $cate_footer = $this->category_model->get_category_footer();
        //kiem tra get subcategory co ton ai hay
        if ($cate_footer) {
             $str .= "<li>";
                    $str .= "   <a href=" . base_url('/') . ">Trang chủ</a>";
                    $str .= "</li>";
            foreach ($cate_footer as $sub_category) {
                if ($sub_category->typepage == 1) {
                    $str .= "<li>";
                    $str .= " <a href=" . base_url('vinh-danh') . ">" . $sub_category->catname . "</a>";
                    $str .= "<li>";
                } else if ($sub_category->typepage == 2) {
                    $str .= "<li>";
                    $str .= " <a href=" . base_url('dac-quyen-vip') . ">" . $sub_category->catname . "</a>";
                    $str .= "<li>";
                } else if ($sub_category->typepage == 3) {
                    $str .= "<li>";
                    $str .= " <a href=" . base_url('lien-he') . ">" . $sub_category->catname . "</a>";
                    $str .= "<li>";
                } else if ($sub_category->typepage == 4) {
                    $str .= "<li>";
                    $str .= " <a href=" . base_url('hoi-dap') . ">" . $sub_category->catname . "</a>";
                    $str .= "<li>";
                } else if ($sub_category->typepage == 5) {
                    $str .= "<li>";
                    $str .= " <a href=" . base_url('cap-do-vip') . ">" . $sub_category->catname . "</a>";
                    $str .= "<li>";
                } else if ($sub_category->typepage == 6) {
                    $str .= "<li>";
                    $str .= " <a href=" . base_url("ho-so-vip") . " id='profilevip'>" . $sub_category->catname . "</a>";
                    $str .= "<li>";
                } else if ($sub_category->typepage == 0) {
                    $str .= "<li>";
                    $str .= " <a href=" . base_url('danh-muc/' . $sub_category->seolink . '-' . $sub_category->id) . ">" . $sub_category->catname . "</a>";
                    $str .= "</li>";
                }

            }
        }
        return $str;
    }
    function cut_string($str, $n_chars, $crop_str = '...') {
        $buff = strip_tags($str);
        if (mb_strlen($buff) > $n_chars) {
            $cut_index = mb_strpos($buff, ' ', $n_chars);
            $buff = mb_substr($buff, 0, ($cut_index === false ? $n_chars : $cut_index + 1), "UTF-8") . $crop_str;
        }
        return $buff;
    }

}
