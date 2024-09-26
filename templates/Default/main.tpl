<!DOCTYPE html>
<html lang="ru">
<head>
	{headers}
	<meta name="HandheldFriendly" content="true">
	<meta name="format-detection" content="telephone=no">
	<meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, width=device-width">
	 <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet" />
	 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
	  <link rel="shortcut icon" href="/img/icon.png" type="image/x-icon">
	 <link rel="stylesheet" href="/css/bootstrap.css" />
	 <link rel="stylesheet" href="/css/reset.css" />
	 <link rel="stylesheet" href="/css/style.css" />
	<!-- Yandex.Metrika counter -->
<script type="text/javascript" >
   (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
   m[i].l=1*new Date();
   for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
   k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
   (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

   ym(98245652, "init", {
        clickmap:true,
        trackLinks:true,
        accurateTrackBounce:true,
        webvisor:true
   });
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/98245652" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->
	<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-VG963NL1JB"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-VG963NL1JB');
</script>
</head>
<body>
	{AJAX}
	<div class="modal fade" class="modal fade" id="price-form" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-center" id="exampleModalTitle">{lang_Получить звонок}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="block">
                        <div class="form">
                            <div class="form-part">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-12 col-md-offset-1 mt-3">
                                            <div class="f-line"></div>
                                            <div class="contact-inner">
												<form method="post" id="sendmail" name="sendmail" action="">
													<p style="margin-top:15px"></p>    <div class="row">
														<div class="col-md-6 col-12 justify-content-center">
															<label class="infomy">{lang_Ваше имя}: <span>*</span> </label>
															<input type="text" name="ad">
														</div>

														<div class="col-md-6 col-12 justify-content-center">
															<label class="infomy">{lang_Ваш телефон}: <span>*</span> </label>
															<input class="elaqe" type="tel" name="phone" maxlength="16">
														</div>

														<div class="col-md-6 col-12  mt-4">
															<div class="button">
																<input type="submit" name="submit3" value="Отправить" id="button" class="btn"></div>
														</div>
													</div>
												</form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<div class="offcanvas offcanvas-end " tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel" aria-modal="true" role="dialog">
        <div class="offcanvas-header">
            <a class="logo" href="/">
                <img width="100"  src="/img/[lang_en]en.png[/lang_en][lang_kg]ru.png[/lang_kg][lang_zh]chin.png[/lang_zh][lang_ru]ru.png[/lang_ru]" class="logo-mobile" alt="Невский">
            </a>
            <div class="btn-close_block">
                <button type="button" class="btn-close_block-item" data-bs-dismiss="offcanvas" aria-label="Close">
                    <svg width="9" height="10" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="#fff">
                        <path d="M.293.293a1 1 0 0 1 1.414 0L8 6.586 14.293.293a1 1 0 1 1 1.414 1.414L9.414 8l6.293 6.293a1 1 0 0 1-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 0 1-1.414-1.414L6.586 8 .293 1.707a1 1 0 0 1 0-1.414z"></path>
                    </svg>
                </button>
            </div>
        </div>
        <div class="offcanvas-body">
            <div class="mobile-menu">
                <ul class="mobile-menu__links">
                    <li><a href="/">{lang_Главная страница}</a></li>
                    <li>
                        <a href="/mission/">{lang_Миссия}</a>
                    </li>
					<li>
                        <a href="/our-values/">{lang_Ценности}</a>
                    </li>
					<li><a href="/o-nas/">{lang_О компании}</a></li>
                    <li>
                        <a href="/products/">{lang_Продукция}</a>
                    </li>
                     <li>
                        <a href="/partners/">{lang_Партнерам}</a>
                    </li>
                    <li>
                        <a href="/investor/">{lang_Инвесторам}</a>
                    </li>
					 <li>
                        <a href="/blogi/">{lang_Блоги}</a>
                    </li>
					
                     <li>
                        <a href="/career/">{lang_Карьера}</a>
                    </li>
					
					 <li>
                            <a href="/land-lease/">{lang_Аренда земли}</a>
                        </li>
					
					 <li>
                            <a href="/purchase-of-land/">{lang_Покупка земли}</a>
                        </li>
					
					
					 <li>
                        <a href="/contact/">{lang_Контакты}</a>
                    </li>
                  <div class="header__languages">
                    {multilanguage mode="link" template="multilanguage" order="code" sort="asc" actualshow="yes"}
                                </div>
                <ul class="mobile-menu__socials socials">
                    <a href="https://wa.me/996998908484" target="_blank">
									<i class="fa-brands fa-whatsapp"></i>
								</a>
								<a href="https://t.me/+996998908484" target="_blank">
									<i class="fa-brands fa-telegram"></i>
								</a>
								<a href="tel:+996702908484">
									<i class="fa-solid fa-phone"></i>
								</a>
								<a href="https://www.facebook.com/profile.php?id=61552783640355" target="_blank">
									<i class="fa-brands fa-facebook"></i>
								</a>
								<a href="https://www.instagram.com/karzaikhamit/"  target="_blank">
									<i class="fa-brands fa-instagram"></i>
								</a>
                </ul>
            </div>
        </div>
    </div>
	   <header data-aos="fade-down" data-aos-duration="300">
            <div class="container">
                <div class="header__content">
                    <div class="header__content-left">
                        <div class="header__logos">
                            <a href="/"><img class="header__logo" src="/img/[lang_en]en.png[/lang_en][lang_kg]ru.png[/lang_kg][lang_zh]chin.png[/lang_zh][lang_ru]ru.png[/lang_ru]" alt="Агро 312" /></a>
                        </div>
                    </div>
					<div class="header__socials-item menu-btn" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight">
						<i class="fa-solid fa-bars" aria-hidden="true"></i>
					</div>
                    <div class="header__content-right">
                        <div class="header__nav">
                            <div class="header__nav-top">
                                <a class="header__btn-contact-us" href="https://wa.me/996998908484">{lang_Связаться с нами}</a>
                                <div class="header__languages">
                                    {multilanguage mode="link" template="multilanguage" order="code" sort="asc" actualshow="yes"}
                                </div>
                            </div>
                            <div class="bottom-nav">
                                <div class="bottom-nav-content">
                                    <nav>
                                        <ul class="nav__ul">
                                            <li>
                                                <a class="no-click main-link" href="#">{lang_О нас}</a>
                                                <ul class="header__drop-menu">
                                                    <li><a href="/mission/">{lang_Миссия}</a></li>
                                                    <li><a href="/our-values/">{lang_Ценности}</a></li>
													<li><a href="/o-nas/">{lang_О компании}</a></li>
                                                </ul>
                                            </li>
                                            <li>
                                                <a class="main-link" href="/products/">{lang_Продукция}</a>
                                                <ul class="header__drop-menu">
                                                       {include file="/engine/modules/katee.php?parent=4"}
                                                </ul>
                                            </li>
                                            <li>
                                                <a class="main-link" href="/partners/">{lang_Партнерам}</a>
                                            </li>
                                            <li>
                                                <a class="main-link" href="/investor/">{lang_Инвесторам}</a>
                                            </li>
                                            <li>
                                                <a class="main-link" href="/career/">{lang_Карьера}</a>
                                            </li>
                                            <li>
                                                <a class="no-click main-link" href="/">{lang_Земля}</a>
                                                <ul class="header__drop-menu">
                                                     <li>
														<a href="/land-lease/">{lang_Аренда земли}</a>
													</li>
                                                    <li><a href="/purchase-of-land/">{lang_Покупка земли}</a></li>
                                                </ul>
                                            </li>
											 <li>
													<a class="main-link"  href="/blogi/">{lang_Блоги}</a>
												</li>
                                            <li>
                                                <a class="main-link" href="/contact/">{lang_Контакты}</a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <a target="_blank" href="https://wa.me/996998908484" class="fixed-whatsapp">
            <i class="fab fa-whatsapp" aria-hidden="true"></i>
        </a>
		[available=main]
			 <section class="slider">
                <div class="swiper mySwiper">
                    <div class="swiper-wrapper">
						  {custom category="1" template="shortstory"}
                    </div>
                    <div class="slider__numbers">
                        <span data-slide-index="0">01</span><span data-slide-index="1">02</span
                        ><span data-slide-index="2" class="active">03</span>
                    </div>
                    
                </div>
            </section>
			<section id="about" class="about-main-business">
                <div class="container about-main-container">
                    <div class="about_holder">
                        {custom category="2" template="shortstory"}
                    </div>
                </div>
            </section>
			<section class="categories">
                <div class="container">
                    <div class="categories__inner">
                        <div
                            class="sectionLink d-flex align-items-start align-items-lg-center justify-content-between flex-column flex-md-row"
                        >
                            <h2 class="categories__title">{lang_Наши продукты}</h2>
                            <a href="/products/" class="btn-all__section">
                                <span>{lang_Посмотреть все продукты}</span>
                                <div class="liquid"></div>
                            </a>
                        </div>

                        <div class="categories__list">
                              {include file="/engine/modules/kate.php?parent=4"}
                        </div>
                    </div>
                </div>
            </section>
			 <section class="members">
                <div class="container">
                    <div class="section-header text-center">
                        <h2 class="fw-bold fs-1">
                            {lang_Наши главные}
                            <span class="b-class-secondary">{lang_преимущество} </span>
                        </h2>
                        <p class="sec-icon mb-4"><i class="fa-solid fa-people-arrows" aria-hidden="true"></i></p>
                    </div>
                    <div class="members-upper mb-5 aos-init" data-aos="fade-right" data-aos-duration="500">
                        <div class="member-card">
                            <div class="number-circle mb-4">
                                <p><i class="fa-solid fa-dice-one"></i></p>
                            </div>
                            <div class="member-info">
                                <h3>
                                   {lang_Качество: Гарантируем высочайший стандарт каждого продукта.}
                                </h3>
                            </div>
                        </div>
                        <div class="member-card">
                            <div class="number-circle mb-4">
                                <p><i class="fa-solid fa-dice-two"></i></p>
                            </div>
                            <div class="member-info">
                                <h3>
                                    {lang_Гарантии: Обеспечиваем высокие стандарты на каждом этапе.}
                                </h3>
                            </div>
                        </div>
                        <div class="member-card">
                            <div class="number-circle mb-4">
                                <p><i class="fa-solid fa-dice-three"></i></p>
                            </div>
                            <div class="member-info">
                                <h3>
                                   {lang_Глобальность: Работаем с международными рынками и партнёрами.}
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div
                        class="members-upper mb-5 members-upper-2 aos-init"
                        data-aos="fade-left"
                        data-aos-duration="1000"
                    >
                        <div class="member-card">
                            <div class="number-circle mb-4">
                                <p><i class="fa-solid fa-dice-four"></i></p>
                            </div>
                            <div class="member-info">
                                <h3>
                                    {lang_Взаимодействие: Мы ценим партнерские отношения и диалог.}
                                </h3>
                            </div>
                        </div>
                        <div class="member-card">
                            <div class="number-circle mb-4">
                                <p><i class="fa-solid fa-dice-five"></i></p>
                            </div>
                            <div class="member-info">
                                <h3>
                                    {lang_Точность: Тщательно следим за качеством на каждом этапе.}
                                </h3>
                            </div>
                        </div>
                        <div class="member-card">
                            <div class="number-circle mb-4">
                                <p><i class="fa-solid fa-dice-six"></i></p>
                            </div>
                            <div class="member-info">
                                <h3>
                                     {lang_Надёжность: Доверие клиентов — наш главный приоритет.}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
			<section class="informations">
                <div class="container">
                    <div
                            class="sectionLink d-flex align-items-start align-items-lg-center justify-content-between flex-column flex-md-row"
                        >
                            <h2 class="categories__title">{lang_Блоги}</h2>
                            <a href="/blogi/" class="btn-all__section">
                                <span>{lang_Посмотреть все блоги}</span>
                                <div class="liquid"></div>
                            </a>
                        </div>
                    <div class="informations-inner">
                        {custom category="3" template="shortstory" limit="3"}
                    </div>
                </div>
            </section>
		[/available]
		
		[not-available=main|cat]
		    {info} {content}
		[/not-available]
		[available=cat]
		[category=4]
		 <section class="section-top-static">
                <div class="container">
                    <div class="section-top-static__text">
                        <a href="/">{lang_Главная}</a>
                        <span>/</span>
                        <h2>{category-title}</h2>
                    </div>
                </div>
                <div class="overlay"></div>
            </section>
			<section class="categories">
                <div class="container">
                    <div class="categories__inner">
                        <div class="categories__list">
                              {include file="/engine/modules/kate.php?parent=4"}
                        </div>
                    </div>
                </div>
            </section>
		[/category]
		
		[category=9-30]
		 	<section class="section-top-static">
                <div class="container">
                    <div class="section-top-static__text">
                        <a href="/">{lang_Главная}</a>
                        <span>/</span>
						<a href="/products/">{lang_Продукты} </a>
                        <span>/</span>
                        <h2>{category-title}</h2>
                    </div>
                </div>
                <div class="overlay"></div>
            </section>
			<section class="categories">
                <div class="container">
                    <div class="categories__inner">
                        <div class="categories__list">
                              {info} {content}
                        </div>
                    </div>
                </div>
            </section>
		[/category]
		
		[category=3]
		 <section class="section-top-static">
                <div class="container">
                    <div class="section-top-static__text">
                        <a href="/">{lang_Главная} </a>
                        <span>/</span>
                        <h2>{category-title}</h2>
                    </div>
                </div>
                <div class="overlay"></div>
            </section>
			<section class="informations">
                <div class="container">
                    <div class="informations-inner">
                        {custom category="3" template="shortstory"}
                    </div>
                </div>
            </section>
		[/category]
		[/available]
		
		<section class="onfooter">
                <div class="container animated zoomIn">
                    <div class="row">
                        <div class="col-md-12">
                            <h2>{lang_Есть вопросы? Свяжитесь с нами!}</h2>
                            <div class="start">
                                <a href="https://wa.me/996998908484" target="_blank">
									<i class="fa-brands fa-whatsapp"></i>
								</a>
								<a href="https://t.me/+996998908484" target="_blank">
									<i class="fa-brands fa-telegram"></i>
								</a>
								<a href="tel:+996702908484">
									<i class="fa-solid fa-phone"></i>
								</a>
								<a href="https://www.facebook.com/profile.php?id=61552783640355" target="_blank">
									<i class="fa-brands fa-facebook"></i>
								</a>
								<a href="https://www.instagram.com/karzaikhamit/"  target="_blank">
									<i class="fa-brands fa-instagram"></i>
								</a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
			 <footer>
            <div class="container-fluid">
                <div class="footer__top">
                    <div class="d-flex gap-3">
                        <a href="/"><img width="150px"  src="/img/[lang_en]en.png[/lang_en][lang_kg]ru.png[/lang_kg][lang_zh]chin.png[/lang_zh][lang_ru]ru.png[/lang_ru]" alt="Logo"></a>
                    </div>
                    <ul>
                        <li>
                            <a href="/mission/">{lang_Миссия}</a>
                        </li>
						<li>
                            <a href="/our-values/">{lang_Ценности}</a>
                        </li>
						<li><a href="/o-nas/">{lang_О компании}</a></li>
                        <li>
                            <a href="/products/">{lang_Продукция}</a>
                        </li>
                    </ul>
                    <ul>
                        <li>
                            <a href="/partners/">{lang_Партнерам}</a>
                        </li>
                        <li>
                            <a href="/investor/">{lang_Инвесторам}</a>
                        </li>
						 <li>
                            <a href="/career/">{lang_Карьера}</a>
                        </li>
                    </ul>
                    <ul class="footer-menu-2">
                       
                        <li>
                            <a href="/land-lease/">{lang_Аренда земли}</a>
                        </li>
						<li>
                            <a href="/purchase-of-land/">{lang_Покупка земли}</a>
                        </li>
						<li>
							 <a   href="/blogi/">{lang_Блоги}</a>
						 </li>
                        <li>
                            <a href="/contact/">{lang_Контакты}</a>
                        </li>
                    </ul>
                    <div class="contact">
					
                        <a class="number number-1" href="tel:+996998908484">
                            <i class="fa-solid fa-phone" aria-hidden="true"></i>+996998908484
                            </a>
							
						 <a class="number number-1" href="tel:+996702908484">
                            <i class="fa-solid fa-phone" aria-hidden="true"></i>+996702908484
                            </a>
							 
                        
                        
                        <a class="email" href="mailto:info@agro312.com"><i class="fa-solid fa-envelope" aria-hidden="true"></i>info@agro312.com</a>
						
						 <a class="email" href="mailto:sales@agro312.com"><i class="fa-solid fa-envelope" aria-hidden="true"></i>sales@agro312.com</a>
						 
						  <a class="email" href="mailto:hr@agro312.com"><i class="fa-solid fa-envelope" aria-hidden="true"></i>hr@agro312.com</a>
                        
                        <!--<a class="footer__btn" href=""> Lorem, ipsum dolor. </a>-->
                    </div>
                    <a class="footer__logo-mobile" href="/">
                        <img width="150" src="/img/atradingWhite.png" alt="">
                    </a>
                </div>
                <!--<div class="footer__bottom">
                    <div class="copyright">
                        <span style="order:2">© Copyright <span class="date">2024</span>. Все права защищены</span>
                        <p style="order:3">
                            <a target="_blank" href="https://okmedia.az/">
                                Сайт разработан:
                                <img width="100" src="" alt="Агро 312">
                            </a>
                        </p>
                        <div style="order:1" class="socials">
                            <a href="https://www.linkedin.com/company/a-tradingazerbaijan" target="_blank">
                                <div class="circle"></div>
                                <i class="fa-brands fa-linkedin" aria-hidden="true"></i>
                            </a>
                            <a href="https://api.whatsapp.com/send?phone=+994502500917&amp;text=Salam, sizə A Trading saytından yazıram.">
                                <div class="circle"></div>
                               <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                            </a>
                            <a href="https://www.instagram.com/atrading_azerbaijan/?igsh=MW00ODAxcGY2cGpvag%3D%3D">
                                <div class="circle"></div>
                                <i class="fa-brands fa-instagram" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>-->
                </div>
            </div>
			<p style="color: #fff;text-align: center">Все права защищены Copyright © 2024.</p>
        </footer>
		
		 <script src="https://kit.fontawesome.com/d4a8753bf6.js" crossorigin="anonymous"></script>
        <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
        <script
            src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
            crossorigin="anonymous"
        ></script>
        <script src="/js/app.js"></script>
</body>
</html>