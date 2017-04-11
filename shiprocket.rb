require 'shopify_api'
require 'sinatra'
require 'httparty'
require 'dotenv'
Dotenv.load

class Shiprocket < Sinatra::Base
  attr_reader :tokens
  API_KEY = ENV['API_KEY']
  API_SECRET = ENV['API_SECRET']
  APP_URL = "8762f527.ngrok.io"

  def initialize
    @tokens = {}
    super
  end

  get '/shiprocket/install' do
    shop = request.params['shop']
	
    scopes = "read_orders,read_products,write_products"

    # construct the installation URL and redirect the merchant
    install_url = "https://#{shop}/admin/oauth/authorize?client_id=#{API_KEY}"\
                "&scope=#{scopes}&redirect_uri=http://#{APP_URL}/shiprocket/auth"
				

    # redirect to the install_url
    redirect install_url
  end

  get '/shiprocket/auth' do
     
	shop = request.params['shop']
    code = request.params['code']
    hmac = request.params['hmac']
	timestamp = request.params['timestamp']

    # perform hmac validation to determine if the request is coming from Shopify
    validate_hmac(hmac,request)
	
	shiprocket_url = "https://app.shiprocket.in/register"

	redirect shiprocket_url
  end
  
  def validate_hmac(hmac,request)
      h = request.params.reject{|k,_| k == 'hmac' || k == 'signature'}
      query = URI.escape(h.sort.collect{|k,v| "#{k}=#{v}"}.join('&'))
      digest = OpenSSL::HMAC.hexdigest(OpenSSL::Digest.new('sha256'), API_SECRET, query)

      unless (hmac == digest)
        return [403, "Authentication failed. Digest provided was: #{digest}"]
      end
    end

end

run Shiprocket.run!