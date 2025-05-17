"use client"

import { useEffect, useState } from "react"
import { useRouter, useSearchParams } from "next/navigation"
import { createClient } from "@/lib/supabase/client"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card"
import { Alert, AlertDescription } from "@/components/ui/alert"
import { AlertCircle, CheckCircle2 } from "lucide-react"

export default function VerifyEmailPage() {
  const router = useRouter()
  const searchParams = useSearchParams()
  const token = searchParams.get("token")
  const type = searchParams.get("type")

  const [isLoading, setIsLoading] = useState(true)
  const [isSuccess, setIsSuccess] = useState(false)
  const [error, setError] = useState<string | null>(null)

  const supabase = createClient()

  useEffect(() => {
    const verifyEmail = async () => {
      if (!token || type !== "email_confirmation") {
        setError("Liên kết xác nhận không hợp lệ hoặc đã hết hạn.")
        setIsLoading(false)
        return
      }

      try {
        const { error } = await supabase.auth.verifyOtp({
          token_hash: token,
          type: "email",
        })

        if (error) {
          setError(error.message)
        } else {
          setIsSuccess(true)
        }
      } catch (err) {
        setError("Đã xảy ra lỗi khi xác nhận email. Vui lòng thử lại.")
      } finally {
        setIsLoading(false)
      }
    }

    verifyEmail()
  }, [token, type, supabase.auth])

  return (
    <div className="flex min-h-screen items-center justify-center bg-gray-50 px-4 py-12 dark:bg-gray-900 sm:px-6 lg:px-8">
      <Card className="w-full max-w-md">
        <CardHeader className="space-y-1">
          <CardTitle className="text-2xl font-bold">Xác nhận email</CardTitle>
          <CardDescription>{isLoading ? "Đang xác nhận email của bạn..." : "Kết quả xác nhận email"}</CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          {isLoading ? (
            <div className="flex justify-center py-4">
              <div className="h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent"></div>
            </div>
          ) : isSuccess ? (
            <Alert className="border-green-500 bg-green-50 text-green-800 dark:bg-green-900 dark:text-green-300">
              <CheckCircle2 className="h-4 w-4" />
              <AlertDescription>
                Email của bạn đã được xác nhận thành công. Bây giờ bạn có thể đăng nhập vào hệ thống.
              </AlertDescription>
            </Alert>
          ) : (
            <Alert variant="destructive">
              <AlertCircle className="h-4 w-4" />
              <AlertDescription>{error}</AlertDescription>
            </Alert>
          )}
        </CardContent>
        <CardFooter>
          <Button className="w-full" onClick={() => router.push("/auth/login")} disabled={isLoading}>
            Đi đến trang đăng nhập
          </Button>
        </CardFooter>
      </Card>
    </div>
  )
}
