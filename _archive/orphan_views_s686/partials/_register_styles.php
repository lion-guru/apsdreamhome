<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;min-height:100vh;background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 50%,#1e293b 100%);display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;padding:2rem 1rem}
body::before{content:'';position:absolute;width:600px;height:600px;background:radial-gradient(circle,rgba(102,126,234,.3) 0%,transparent 70%);top:-200px;right:-100px;border-radius:50%}
body::after{content:'';position:absolute;width:500px;height:500px;background:radial-gradient(circle,rgba(118,75,162,.25) 0%,transparent 70%);bottom:-150px;left:-100px;border-radius:50%}
.register-wrapper{position:relative;z-index:1;width:100%;max-width:560px}
.brand-section{text-align:center;margin-bottom:1.5rem}
.brand-logo{width:68px;height:68px;background:linear-gradient(135deg,#0d9488,#0f766e);border-radius:18px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:.75rem;box-shadow:0 8px 25px rgba(102,126,234,.4)}
.brand-logo i{font-size:1.8rem;color:#fff}
.brand-section h1{color:#fff;font-size:1.5rem;font-weight:700}
.brand-section p{color:rgba(255,255,255,.5);font-size:.88rem;margin-top:.2rem}
.register-card{background:rgba(255,255,255,.98);border-radius:20px;padding:0;box-shadow:0 20px 60px rgba(0,0,0,.3);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.3);position:relative;overflow:hidden}
.register-card::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,#0d9488,#059669,#10b981,#6366f1,#0d9488);background-size:200% 100%;animation:shimmer 3s ease-in-out infinite}
@keyframes shimmer{0%{background-position:-200% 0}100%{background-position:200% 0}}
@keyframes fadeInUp{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}
.role-tabs{display:flex;border-bottom:2px solid #f1f5f9}
.role-tab{flex:1;padding:1rem .5rem;text-align:center;cursor:pointer;transition:all .3s ease;border-bottom:3px solid transparent;margin-bottom:-2px;background:none;border-top:none;border-left:none;border-right:none;font-family:inherit}
.role-tab:hover{background:#f8fafc}
.role-tab.active{border-bottom-color:var(--tab-color)}
.role-tab .tab-icon{width:44px;height:44px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:.4rem;transition:all .3s ease;background:#f1f5f9;color:#64748b}
.role-tab.active .tab-icon{background:var(--tab-color);color:#fff;box-shadow:0 4px 12px var(--tab-shadow)}
.role-tab .tab-label{display:block;font-size:.78rem;font-weight:600;color:#64748b;transition:color .3s}
.role-tab.active .tab-label{color:var(--tab-color)}
.role-tab[data-role="customer"]{--tab-color:#0d9488;--tab-shadow:rgba(13,148,136,.3)}
.role-tab[data-role="agent"]{--tab-color:#059669;--tab-shadow:rgba(5,150,105,.3)}
.role-tab[data-role="associate"]{--tab-color:#ea580c;--tab-shadow:rgba(234,88,12,.3)}
.form-body{padding:1.75rem 2rem 2rem}
.form-section-title{font-size:.78rem;font-weight:700;color:#0d9488;text-transform:uppercase;letter-spacing:1.2px;margin-bottom:.75rem;display:flex;align-items:center;gap:.5rem}
.form-section-title::after{content:'';flex:1;height:1px;background:linear-gradient(to right,#e2e8f0,transparent)}
.input-group-custom{position:relative;margin-bottom:.85rem}
.input-group-custom>i{position:absolute;left:14px;top:2.35rem;color:#94a3b8;font-size:.85rem;z-index:5;pointer-events:none;transition:color .2s}
.input-group-custom .form-control:focus~i,.input-group-custom .form-select:focus~i{color:#0d9488}
.form-label-custom{font-size:.82rem;font-weight:600;color:#334155;margin-bottom:.3rem;display:block}
.form-control,.form-select{border:1.5px solid #e2e8f0;border-radius:10px;padding:.6rem .9rem .6rem 2.5rem;font-size:.9rem;transition:all .3s ease;background:#f8fafc;height:46px}
.form-control:focus,.form-select:focus{border-color:#0d9488;box-shadow:0 0 0 .2rem rgba(13,148,136,.1);background:#fff}
.form-select{padding-left:2.5rem}
.role-extra{display:none}
.role-extra.active{display:block;animation:fadeIn .3s ease}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
.required-badge{color:#dc2626;font-weight:700}
.optional-badge{font-size:.7rem;color:#94a3b8;font-style:italic}
.btn-register{width:100%;height:50px;border:none;border-radius:12px;color:#fff;font-size:1rem;font-weight:600;cursor:pointer;transition:all .3s ease;position:relative;overflow:hidden;margin-top:.5rem}
.btn-register::before{content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.2),transparent);transition:left .5s ease}
.btn-register:hover::before{left:100%}
.btn-register:hover{transform:translateY(-2px);box-shadow:0 8px 25px var(--btn-shadow)}
.btn-register:active{transform:translateY(0)}
.btn-register.loading{pointer-events:none;opacity:.8}
.btn-register[data-role="customer"]{background:linear-gradient(135deg,#0d9488,#0f766e);--btn-shadow:rgba(13,148,136,.4)}
.btn-register[data-role="agent"]{background:linear-gradient(135deg,#059669,#10b981);--btn-shadow:rgba(5,150,105,.4)}
.btn-register[data-role="associate"]{background:linear-gradient(135deg,#ea580c,#f97316);--btn-shadow:rgba(234,88,12,.4)}
.error-box{background:linear-gradient(135deg,#fef2f2,#fee2e2);border:1px solid #fecaca;border-radius:12px;padding:1rem 1.25rem;margin-bottom:1.25rem;animation:slideInDown .4s ease}
.error-box .error-title{color:#dc2626;font-weight:700;font-size:.85rem;margin-bottom:.4rem}
.error-box ul{margin:0;padding-left:1.25rem}
.error-box li{color:#991b1b;font-size:.83rem;margin-bottom:.15rem}
@keyframes slideInDown{from{opacity:0;transform:translateY(-20px)}to{opacity:1;transform:translateY(0)}}
.terms-text{font-size:.78rem;color:#94a3b8;line-height:1.5;text-align:center;margin-bottom:1rem}
.terms-text a{color:#0d9488;text-decoration:none}
.login-section{text-align:center;padding:1rem 2rem 1.5rem}
.login-section p{color:rgba(255,255,255,.55);font-size:.88rem}
.login-section a{color:#5eead4;text-decoration:none;font-weight:600}
.login-section a:hover{color:#99f6e4}
.back-home{display:block;text-align:center;margin-top:.75rem;color:rgba(255,255,255,.35);text-decoration:none;font-size:.82rem;transition:color .2s}
.back-home:hover{color:rgba(255,255,255,.7)}
.divider-line{height:1px;background:linear-gradient(to right,transparent,#e2e8f0,transparent);margin:0 2rem}
.sponsor-note{background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;padding:.6rem .8rem;margin-top:.5rem;font-size:.8rem;color:#9a3412;display:flex;align-items:flex-start;gap:.5rem}
@media(max-width:576px){body{padding:1rem .5rem;align-items:flex-start;padding-top:1.5rem}.form-body{padding:1.5rem 1.25rem}.brand-section h1{font-size:1.3rem}.role-tab .tab-icon{width:38px;height:38px;font-size:.9rem}.role-tab .tab-label{font-size:.7rem}}
</style>
